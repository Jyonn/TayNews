function postJSON(url, json_str, success_function, error_function=null) {
    $.ajax({
        url : url,
        type : 'POST',
        data : json_str,
        async: true,
        dataType : 'json',
        contentType : 'application/text',
        success : success_function,
        error: error_function,
    });
}

var ok_img = '<img src="resource/img/ok.png">',
	channel_list = $('.channel-list'),
	is_login = false,
	global_token = null;

function Channel(channel) {
	this.channel = channel.channel;
	this.id = channel.id;
	this.state = channel.state;
	this.start = -1;
	this.is_over = false;
}

var channel_arr = new Array();

function email_captcha_loop(param_package) {
    if (param_package.num <= 0) {
        clear_interval(param_package);
        return;
    }
    param_package.btn.text(param_package['num'] + '秒后重发');
    param_package.num--;
}

function _email_captcha_loop(param_package) {
    return function () {
        email_captcha_loop(param_package)
    }
}

function clear_interval(param_package) {
    clearInterval(parseInt(param_package.clock.clock));
    param_package.btn.attr("disabled", false);
    param_package.btn.text('获取验证码');
}

function set_clock_interval(btn) {
    const param_package = {
        num: 30,
        btn: btn,
        clock: {clock: ''}
    };
    btn.attr("disabled", true);
    param_package.clock.clock = setInterval(_email_captcha_loop(param_package), 1000);
    return param_package
}

function get_format_time(timestamp) {
	if (timestamp == false)
		return '';
	var time = new Date(timestamp * 1000);
	time_str = '';
	if (time.getMonth() < 10)
		time_str += '0';
	time_str += time.getMonth() + '/';
	if (time.getDate() < 10)
		time_str += '0';
	time_str += time.getDate() + ' ';
	if (time.getHours() < 10)
		time_str += '0';
	time_str += time.getHours() + ':';
	if (time.getMinutes() < 10)
		time_str += '0';
	time_str += time.getMinutes();
	return time_str;
}

function News(news) {
	this.channel = news.channel;
	this.id = news.id;
	this.pic = news.pic;
	this.title = news.title;
	this.url = news.url;
	this.time_str = get_format_time(news.publish_time);
}

var news_arr = new Array();

function change_channel_state(channel) {
	var id = channel.attr('data-id'),
		border = channel.find('.checkbox-border');
	for (var i = 0; i < channel_arr.length; i++) {
		var channel = channel_arr[i];
		if (id == channel.id) {
			channel.state = !channel.state;
			if (channel.state)
				border.html(ok_img);
			else
				border.empty();
		}
	}
	get_news();
	check_scroll();
}
	
function set_channel_item_listener() {
	var channel_item = $('.channel-item');
	channel_item.on('click', function() {
		change_channel_state($(this));
	});
}

var news_container = $('.news-container');

function get_info() {
	postJSON('user/info.php', "", function(response) {
		is_login = response.body.is_login;
		nav_user.text(is_login ? '个人中心' : '登录');
		if (!is_login)
			nav_register.css('display', 'inherit');
		channel_list.empty();
		for (var i = 0; i < response.body.channel_state.length; i++) {
			var item = response.body.channel_state[i],
				html =	'<label class="press channel-item" data-status="' + item.state + '" data-id="' + item.id + '">' + 
						'	<p class="checkbox-border">' + (item.state ? ok_img : '') + '</p>' + 
						'	<p class="checkbox-value">' + item.channel + '</p>'; + 
						'</label>';
			channel_list.append(html);
			channel_arr.push(new Channel(item));
		}
		set_channel_item_listener();
		
		get_news();
		
		window.onscroll = function() {
			check_scroll();
		};
	});
}

function check_scroll() {
	if (global_token == 'random_poem')
		return;
	if ($(document).height() - $(window).scrollTop() - $(window).height() < 1000)
		get_news()
}

function learn_more(index) {
	if (is_login) {
		var post = {channel: news_arr[index].channel},
		json = $.toJSON(post);
		postJSON('content/count.php', json);
	}
	window.open(news_arr[index].url);
}

function refresh_news_list(token, news_arr) {
	if (token != global_token)
		return;
	news_container.empty();
	
	var count = 0;
	for (var i = 0; i < news_arr.length; i++)
		for (var j = 0; j < channel_arr.length; j++)
			if (news_arr[i].channel == channel_arr[j].id) {
				if (channel_arr[j].state) {
					count ++;
					var html =	'<div class="news-item slow col-md-4 col-sm-6 col-xs-12">' + 
								'	<img src="' + news_arr[i].pic + '">' + 
								'	<div class="channel">' + channel_arr[j].channel + '</div>' + 
								'	<div><b>/</b></div>' + 
								'	<div class="title">' + news_arr[i].title + '</div>' + 
								'	<div class="publish-time">' + news_arr[i].time_str + '</div>' + 
								'	<div class="learn-more slow press round-btn" onclick="learn_more('+i+')">阅读更多</div>' + 
								'</div>';
					news_container.append(html);
				}
				break;
			}
	if (count == 0)
		random_poem();
}

function get_news() {
	var post = {
		count: 10,
		channel_list: []
	};
	var token = Math.random().toString(36).substr(2);
	global_token = token;
	for (var i = 0; i < channel_arr.length; i++) {
		var channel = channel_arr[i];
		if (channel.state)
			post.channel_list.push({channel: channel.id, start: channel.start});
	}
	var json = $.toJSON(post);
	postJSON('content/get.php', json, function(response) {
		var info = response.body.info;
		for (var i = 0; i < info.length; i++) {
			for (var j = 0; j < channel_arr.length; j++)
				if (info[i].channel == channel_arr[j].id) {
					channel_arr[j].start = info[i].news_start;
					channel_arr[j].is_over = info[i].is_over;
				}
		}
		var news_list = response.body.news_list;
		for (var i = 0; i < news_list.length; i++) {
			if (news_list[i].pic.length < 5)
				news_list[i].pic = "resource/img/taynews.png";
			news_arr.push(new News(news_list[i]));
		}
		refresh_news_list(token, news_arr);
	});
}

var nav_search = $('#nav-search'),
	search_container = $('#search-container'),
	search_input = $('#search-input'),
	nav_user = $('#nav-user'),
	login_container = $('#login-container'),
	email_input = $('#email-input'),
	password_input = $('#password-input'),
	search_btn = $('#search-btn'),
	login_btn = $('#login-btn'),
	user_container = $('#user-container'),
	like_btn = $('#like-btn'),
	logout_btn = $('#logout-btn'),
	nav_home = $('#nav-home'),
	nav_register = $('#nav-register'),
	register_container = $('#register-container'),
	email_r_input = $('#email-r-input'),
	captcha_r_input = $('#captcha-r-input'),
	password_r_input = $('#password-r-input'),
	send_captcha_btn = $('#send-captcha-btn'),
	register_btn = $('#register-btn');

function not_focus_fadeout(fade_out_mask, focus_list) {
	setTimeout(function(){
		for (var i = 0; i < focus_list.length; i++)
			if (document.activeElement == document.getElementById(focus_list[i])) {
				return;
			}
		fade_out_mask.fadeOut('250');
	}, 100);
	return;
}

function not_focus_fadeout_for_register() {
	not_focus_fadeout(register_container, 
		['email-r-input', 'captcha-r-input', 'password-r-input', 'send-captcha-btn', 'register-btn', 'register-container']);
}

function not_focus_fadeout_for_search() {
	not_focus_fadeout(search_container, ['search-input', 'search-btn', 'search-container']);
}

function not_focus_fadeout_for_login() {
	not_focus_fadeout(login_container, ['email-input', 'login-btn', 'password-input', 'login-container']);
}

function not_focus_fadeout_for_user() {
	not_focus_fadeout(user_container, ['like-btn', 'logout-btn']);
}

var is_sending_captcha = false,
	is_geting_news = false;

function set_nav_listeners() {	
	nav_search.on('click', function() {
		search_container.fadeIn('250');
		search_input.focus();
	});
	
	search_input.on('blur', not_focus_fadeout_for_search);
	search_btn.on('blur', not_focus_fadeout_for_search);
	search_container.on('blur', not_focus_fadeout_for_search);
	
	nav_user.on('click', function() {
		if (!is_login) {
			login_container.fadeIn('250');
			email_input.focus();
		}
		else {
			user_container.fadeIn('250');
			like_btn.focus();
		}
	});
	
	like_btn.on('blur', not_focus_fadeout_for_user);
	logout_btn.on('blur', not_focus_fadeout_for_user);
	
	email_input.on('blur', not_focus_fadeout_for_login);
	password_input.on('blur', not_focus_fadeout_for_login);
	login_btn.on('blur', not_focus_fadeout_for_login);
	login_container.on('blur', not_focus_fadeout_for_login);
	
	nav_home.on('click', function() {
		document.location.reload();
	});
	
	nav_register.on('click', function() {
		register_container.fadeIn('250');
		email_r_input.focus();
	});
	
	email_r_input.on('blur', not_focus_fadeout_for_register);
	password_r_input.on('blur', not_focus_fadeout_for_register);
	captcha_r_input.on('blur', not_focus_fadeout_for_register);
	send_captcha_btn.on('blur', not_focus_fadeout_for_register);
	register_btn.on('blur', not_focus_fadeout_for_register);
	register_container.on('blur', not_focus_fadeout_for_register);
	
	logout_btn.on('click', function() {
		postJSON('user/logout.php', "", function(response) {
			document.location.reload();
		});
	});
	
	login_btn.on('click', function() {
		var post = {
			email: email_input.val(),
			pass: password_input.val()
		},
			json = $.toJSON(post),
			login_hint = $('#login-hint');
		login_hint.text('');
		
		postJSON('user/login.php', json, function(response) {
			if (response.code == 0) {
				document.location.reload();
			}
			else {
				login_hint.text(response.msg);
			}
		});
	});
	
	like_btn.on('click', function() {
		var post = {
			channel_list: []
		};
		var token = Math.random().toString(36).substr(2);
		global_token = token;
		for (var i = 0; i < channel_arr.length; i++) {
			var channel = channel_arr[i];
			post.channel_list.push({channel: channel.id, start: channel.start});
		}
		var json = $.toJSON(post),
			tmp_news_arr = new Array();
		postJSON('content/like.php', json, function(response) {
			var info = response.body.info;
			for (var i = 0; i < info.length; i++) {
				for (var j = 0; j < channel_arr.length; j++)
					if (info[i].channel == channel_arr[j].id) {
						channel_arr[j].start = info[i].news_start;
						channel_arr[j].is_over = info[i].is_over;
					}
			}
			var news_list = response.body.news_list;
			for (var i = 0; i < news_list.length; i++) {
				if (news_list[i].pic.length < 5)
					news_list[i].pic = "resource/img/taynews.png";
				tmp_news_arr.push(new News(news_list[i]));
			}
			refresh_news_list(token, tmp_news_arr);
		});
	});
	
	search_btn.on('click', function() {
		var post = {
			keyword: $('#search-input').val()
		},
			json = $.toJSON(post);
		var token = Math.random().toString(36).substr(2);
			global_token = token;
		var tmp_news_arr = new Array();
		postJSON('content/search.php', json, function(response) {
			var news_list = response.body;
			for (var i = 0; i < news_list.length; i++) {
				if (news_list[i].pic.length < 5)
					news_list[i].pic = "resource/img/taynews.png";
				tmp_news_arr.push(new News(news_list[i]));
			}
			refresh_news_list(token, tmp_news_arr);
			login_container.fadeOut('250');
		});
	});
	
	send_captcha_btn.on('click', function() {
		if (is_sending_captcha)
			return;
		is_sending_captcha = true;
		var post = {
			email: email_r_input.val()
		},
			json = $.toJSON(post),
			register_hint = $('#register-hint');
		register_hint.text('');
		var param_package = set_clock_interval(send_captcha_btn);
		postJSON('user/email.php', json, function(response) {
			if (response.code != 0) {
                clear_interval(param_package);
                register_hint.text(response.msg);
            }
			is_sending_captcha = false;
		}, function() {
			is_sending_captcha = false;
		});
	});
	
	register_btn.on('click', function() {
		var post = {
			code: captcha_r_input.val(),
			pass: password_r_input.val()
		},
			json = $.toJSON(post),
			register_hint = $('#register-hint');
		register_hint.text('');
		
		postJSON('user/register.php', json, function(response) {
			if (response.code == 0) {
				document.location.reload();
			}
			else {
				register_hint.text(response.msg);
			}
		});
	});
}

function random_poem() {
	global_token = 'random_poem';
	postJSON('content/random.php', '', function(response) {
		content = Base64.decode(response.body.content);
		writer = response.body.writer_name;
		work = response.body.work_name;
		news_container.empty();
		var html =	'<div id="poem-title">' + work + '</div>' + 
					'<div id="poem-writer">文 / ' + writer + '</div>' + 
					'<textarea id="poem-content" readonly>' + content + '</textarea>';
		news_container.html(html);
		var poem_content = document.getElementById("poem-content");
		poem_content.style.height = poem_content.scrollHeight + 'px';
	});
}

$(document).ready(function() {
	get_info();
	set_nav_listeners();
});
