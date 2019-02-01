var fd = 0;//我的swoole fd编号
var name = '';//用户昵称

var data = [];

var ws = new WebSocket("ws://60.205.207.244:5950");
			
ws.onopen = function(){
	console.log('连接成功');
};

ws.onmessage = function (evt) 
{ 
	var received_msg = evt.data;
	swoole_data = JSON.parse( received_msg );
	switch(swoole_data.type){
		case 'setFd'://连接成功获取到自己的fd
			fd = swoole_data.fd;
			break;
		case 'set_data_succ'://名称设置成功
			setSucc();
			break;
		case 'msg'://消息
			addMsg(swoole_data.data);
			break;
		case 'getUserAll'://获取所有在线用户
			data = swoole_data.data;
			userList(data);
			break;
		case 'addUser'://有用户上线
			data.push(swoole_data.data);
			userList(data);
			break;
		case 'close_notice'://用户断开链接
			delUser(swoole_data.fd);
			break;
		default:
			break;
	}
};

ws.onclose = function()
{ 
	console.log('连接关闭');
};

$('#setData').click(function(){
	name = $('#name').val();
	if(name){
		var userData = {};
		userData.type = 'setData';
		userData.name = name;
		userData.icon = '';
		userData.id = '';
		ws.send(JSON.stringify( userData ));
	}else{
		alert('请输入用户名');
	}
})

//设置成功后获取用户列表
function setSucc(){
	$('.myName').hide();
	var getUser = {};
	getUser.type = 'getUserAll';
	ws.send(JSON.stringify( getUser ));
}

//生成用户列表
function userList(user){
	var html = "";
	for (var i = 0; i < user.length; i++) {
		if(user[i].fd != fd){
			html += "<li data-fd='" + user[i].fd + "'>" + "<i class='iconfont'>&#xe752;</i>" + "<p>" + user[i].name + "</p>" + "</li>";
		}
	}
	$(".chatbar-contacts-uls").html(html);
}
userList(data);

//删除用户
function delUser(close_fd){
	$('.chatbar-contacts-uls').children('li').each(function(){
		var child_fd = $(this).attr('data-fd');
		if(close_fd == child_fd){
			$(this).remove();
		}
	});
	
	for(var i = 0;i<data.length ;i++){
		if(data[i]['fd'] == close_fd){
			data.splice(i,1);
		}
	}
}

//接收到消息
function addMsg(user){
    var messages_text = $(".messages-text");
	var str = "<ul class='messages-text-uls'><li class='messages-text-lis'>" 
                + "<h4><i></i><span>" + user.user.name + "</span><span class='time'>"
                + user.time + "</span></h4>" + "<p>" + user.msg + "</p>"
                + "</ul></li>";
        messages_text.append(str);
}


//点击按钮下拉
$(".icon").on('click', function() {
    if ($(".chatbar").is(":visible")) {
        $(".chatbar").slideUp();
        $(".icon-box").removeClass('shadow');
    } else {
        $(".chatbar").slideDown();
        $(".icon-box").addClass('shadow');
    }
});

$(".chatbar-contacts-uls").on('click','li',function() {
	var fd = $(this).attr('data-fd');
	$('#fd').val(fd);
    var text = $(this).find('p').text();
    $(".chatbar-messages").css({
        "transform": "translate3d(0, 0, 0)"
    });
    $('.messages-title h4').text(text);
	$(".messages-text").html('');
});

$(".return-icon").click(function() {
    $(".chatbar-messages").css({
        "transform": "translate3d(100%, 0, 0)"
    });
});

//发送消息
$(".message-btn").on('click', function() {
    var message = $('.messages-content').val();
    var toFd = $('#fd').val();
    var messages_text = $(".messages-text");
    var timer = time();
    if (message != "undefined" && message != '') {
        var str = "<ul class='messages-text-uls'><li class='messages-text-lis'>" 
                + "<h4><i></i><span>" + name + "</span><span class='time'>"
                + timer + "</span></h4>" + "<p>" + message + "</p>"
                + "</ul></li>";
        messages_text.append(str);
    } else {
        var messageTooltip = "<div class='message-tooltip'>不能发送空白信息</div>";
        $("body").append(messageTooltip);
        setTimeout(function() {
            $(".message-tooltip").hide();
        }, 2000);
    }
	$('.messages-content').val('');
	
	var obj = {};
	obj.type = 'msg';
	obj.msg = message;
	obj.toFd = toFd;
	ws.send(JSON.stringify( obj ));
});

//时间封装
function time(type,timer = false) {
    type = type || 'hh:mm'
	if(!timer){
		var timer = new Date();
	}
    var year = timer.getFullYear();
    var month = timer.getMonth() + 1;
    var date = timer.getDate();
    var hour = timer.getHours();
    var min = timer.getMinutes();
    if (type == 'hh:mm') {
        hour = hour < 10 ? ('0' + hour) : hour;
        min = min < 10 ? ('0' + min) : min;
    }
    var time = year + "/" + month + "/" + date + "  " + hour + ":" + min;
    return time;
}

//搜索功能
$('.search-text').on('keyup', function() {
    var txt = $('.search-text').val();
    txt = txt.replace(/\s/g, '');
    $('.chatbar-contacts-uls li').each(function() {
        if (!$(this).is(':contains(' + txt + ')')) {
            $(this).hide();
        } else {
            $(this).show();
        }
    });
    return false;
});
