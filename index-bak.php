<?php
session_start();
if(isset($_SESSION['username']))
{
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Chat</title>
<script type="text/javascript" src="jquery-3.4.1.min.js"></script>
<script type="text/javascript">
function WSConnection(websocketURL)
{
	this.websocketURL = websocketURL;
	this.socket = null;
	this.connected = false;
	this.reconnectSocketDelay = 1000;
	this.reconnectTimeout = null;
	this.firstConnect = true;
	this.connect = function(websocketURL)
	{
		websocketURL = websocketURL || this.websocketURL;
		console.log('Connecting to socket');
		this.socket = null;
		this.socket = new WebSocket(websocketURL);
		this.socket.onopen = function(event) 
		{
			_this.connected = true;
			if(_this.firstConnect)
			{
				_this.onFirstConnect(event);
			}
			else
			{
				_this.onReconnect(event);
				// Prevent executed twice
				_this.onReconnect = function(event){};
			}
			_this.firstConnect = false;
			_this.onOpen(event);
		};
	
		this.socket.onmessage = function(event) 
		{
			_this.onMessage(event);
		};
	
		this.socket.onclose = function(event) 
		{
			console.log('Disconnected');
			_this.connected = false;
			_this.onClose(event);
			if (event.wasClean) 
			{
			} 
			else 
			{
			}
			clearTimeout(_this.reconnectTimeout);
			_this.reconnectTimeout = setTimeout(function()
			{
				_this.connect();
			}, _this.reconnectSocketDelay);
		};
		this.socket.onerror = function(error) 
		{
			_this.connected = false;
			_this.onError(error);
			clearTimeout(_this.reconnectTimeout);
			_this.reconnectTimeout = setTimeout(function()
			{
				_this.connect();
			}, _this.reconnectSocketDelay);
		};
	}
	this.onOpen = function(event)
	{
	};
	this.onFirstConnect = function(event)
	{
	};
	this.onReconnect = function(event)
	{
	};
	this.send = function(message)
	{
		try
		{
			if(this.connected)
			{
				this.socket.send(message);
			}
			else
			{
				this.onReconnect = function(event)
				{
					this.socket.send(message);
				};
				this.connect();
			}
		}
		catch(e)
		{
			this.onReconnect = function(event)
			{
				this.socket.send(message);
			};
			this.connect();			
		}
	};
	this.onMessage = function(event)
	{
	};
	this.onClose = function(event)
	{
	};
	this.onError = function(error)
	{
	};
	var _this = this;
}
var wsURL = 'ws://localhost:8888';
var ws = new WSConnection(wsURL);
ws.onFirstConnect = function(event)
{
}
ws.onOpen = function(event)
{
}
ws.onClose = function(event)
{
}
ws.onError = function(event)
{
}
ws.onMessage = function(event)
{
	processMessage(event.data);
}
ws.connect();


function processMessage(message)
{
	var json = JSON.parse(message);
	var command = json.command;
	var data = json.data[0];
	var html = '';
	if(command == 'user-on-system')
	{
		// Iterate data.users and insert into list if not exists
		for (var [username, userData] of Object.entries(data.users)) {
			html = buildUserResource(username, userData);
			if($('.user-list li[data-username="'+username+'"]').length == 0)
			{
				$('.user-list').prepend(html);
			}
		}
		// Iterate list and remove if not in data.users
		$('.user-list li').each(function(index, element) {
            var username = $(this).attr('data-username');
			if(typeof data.users[username] == 'undefined')
			{
				$(this).remove();
			}
        });
		
	}
}
function buildUserResource(username, userData)
{
	var html = '<li class="user-item user-item-'+userData.sex+'" data-username="'+username+'" data-name="'+userData.full_name+'"><a href="#'+username+'"><div>'+username+'</div><div>'+userData['full_name']+'</div></a></li>';
	return html;
}
</script>
<style type="text/css">
body{
	margin:0;
	padding:0;
}
.all{
	padding:10px;
	box-sizing:border-box;
}
.wrapper{
	position:relative;
	padding-left:260px;
}
.sidebar{
	position:absolute;
	margin-left:-260px;
	width:250px;
	border:1px solid #DDDDDD;
	box-sizing:border-box;
}
.main{
	border:1px solid #DDDDDD;
	box-sizing:border-box;
}
.user-list{
	margin:0;
	padding:0;
	list-style-type:none;
}
.user-list li{
	margin:0;
	padding:0;
	display:block;
}
.user-list li a{
	display:block;
	padding:10px 10px;
	border-bottom:1px solid #DDDDDD;
}
.user-list li:last-child a{
	border-bottom:none;
}
</style>
</head>

<body>
<div class="all">
<div class="wrapper">
  <div class="sidebar">
    <div class="user-list-area">
        <ul class="user-list">
        </ul>
    </div>
  </div>
  <div class="main">
    Main content
  </div>
</div>
</div>


</body>
</html>
<?php
}
else
{
	require_once "login-form.php";
}
?>