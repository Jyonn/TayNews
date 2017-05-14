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
	refresh_news_list(global_token, news_arr);
	check_scroll();
}
	
function set_channel_item_listener() {
	var channel_item = $('.channel-item');
	channel_item.on('click', function() {
		change_channel_state($(this));
	});
}
	
function get_info() {
	postJSON('user/info.php', "", function(response) {
		is_login = response.body.is_login;
		nav_user.text(is_login ? '个人中心' : '登录');
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
	});
}

var news_container = $('.news-container');

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
	
	for (var i = 0; i < news_arr.length; i++)
		for (var j = 0; j < channel_arr.length; j++)
			if (news_arr[i].channel == channel_arr[j].id) {
				if (channel_arr[j].state) {
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



function check_scroll() {
    news_container.each(function () {
        if (this.scrollHeight - this.scrollTop - news_container.height() < 1000)
            get_news();
    })
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
	nav_home = $('#nav-home');

function set_nav_listeners() {	
	nav_search.on('click', function() {
		search_container.fadeIn('250');
		search_input.focus();
	});
	
	search_input.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('search-btn') &&
				document.activeElement != document.getElementById('search-container')) {
				search_container.fadeOut('250');
			}
		}, 100);
	});
	
	search_btn.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('search-input') &&
				document.activeElement != document.getElementById('search-container')) {
				search_container.fadeOut('250');
			}
		}, 100);
	});
	
	search_container.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('search-input') &&
				document.activeElement != document.getElementById('search-btn')) {
				search_container.fadeOut('250');
			}
		}, 100);
	});
	
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
	
	like_btn.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('logout-btn')) {
				user_container.fadeOut('250');
			}
	   }, 100);
	});
	
	logout_btn.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('like-btn')) {
				user_container.fadeOut('250');
			}
	   }, 100);
	});
	
	email_input.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('login-btn') &&
				document.activeElement != document.getElementById('password-input') &&
				document.activeElement != document.getElementById('login-container')) {
				login_container.fadeOut('250');
			}
	   }, 100);
	});
	
	password_input.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('login-btn') &&
				document.activeElement != document.getElementById('email-input') &&
				document.activeElement != document.getElementById('login-container')) {
				login_container.fadeOut('250');
			}
		}, 100);
	});
	
	login_btn.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('password-input') &&
				document.activeElement != document.getElementById('email-input') &&
				document.activeElement != document.getElementById('login-container')) {
				login_container.fadeOut('250');
			}
		}, 100);
	});
	
	login_container.on('blur', function() {
		setTimeout(function(){
			if (document.activeElement != document.getElementById('password-input') &&
				document.activeElement != document.getElementById('email-input') &&
				document.activeElement != document.getElementById('login-btn')) {
				login_container.fadeOut('250');
			}
		}, 100);
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
	
	login_btn.on('click', function() {
		var post = {
			email: email_input.val(),
			pass: password_input.val()
		},
			json = $.toJSON(post);
		postJSON('user/login.php', json, function(response) {
			if (response.code == 0) {
				document.location.reload();
			}
		});
	});
	
	logout_btn.on('click', function() {
		postJSON('user/logout.php', "", function(response) {
			document.location.reload();
		});
	});
	
	nav_home.on('click', function() {
		document.location.reload();
	});
}

$(document).ready(function() {
	get_info();
	
	set_nav_listeners();
	
	news_container.on('scroll', function () {
        if (this.scrollHeight - this.scrollTop - news_container.height() < 1000)
            get_news();
    });
	
	check_scroll();
});
