var reconnectDelay = 4000;
var resendDelay = 200;
var markSendDelay = 100;
var chatBoxWidth = 270;
var bounceDuration = 4000;
var hideChatMenuDelay = 6000;
var feedbackRetryDelay = 300;
var localStorageName = 'current';
var callStatus = 'IDDLE';
var callIddleTimeout = setTimeout(function(){}, 100);
var callIddleDelay = 5000;
var repeatRingtoneDelay = 3000;
var missedCallTimeout = setTimeout(function(){}, 100);
var delayToMissedCall = 30000;
var candidatePartnerID = '';
var candidateChatRoom = '';
var currentChat = [];
var ringtoneInterval = null;
var pc = null;
var sendOnCallInterval = null;
var sendOnCallDelay = 30000;
var localStream = null;
var localVideo = null;
var remoteVideo = null;
var configuration = {
    'iceServers': [
		{
            'urls': 'stun:stun.stunprotocol.org:3478'
        },
        {
            'urls': 'stun:stun.l.google.com:19302'
        },
		{
			'urls': 'stun:stun.services.mozilla.com'
		}
    ]
};
function PlanetMessage()
{
	this.myID = '';
	this.messages = {};
	this.partners = {};
	this.unreadMessage = {};
	this.addMessage = function(message, callbackFunctions)
	{
		var i, data;
		var obj = JSON.parse(message);		
		var callbackFunction = callbackFunctions[obj.command];
		if(obj.command == 'send-message')
		{
			if(obj.data.length)
			{
				for(i in obj.data)
				{
					data = obj.data[i];
					var partner_id = data.partner_id;
					if(typeof this.messages[partner_id] == 'undefined')
					{
						this.messages[partner_id] = [];
						this.unreadMessage[partner_id] = [];
					}
					this.messages[partner_id].push(data);
					
					this.partners[partner_id] = {partner_id:data.partner_id, partner_uri:data.partner_uri, partner_name:data.partner_name};
					if(typeof callbackFunction == 'function')
					{
						callbackFunction(partner_id, data);
					}
				}
			}
		}
		else if(obj.command == 'log-in')
		{
			this.myID = obj.data[0].my_id;
			if(typeof callbackFunction == 'function')
			{
				callbackFunction(this.myID, obj.data[0]);
			}
		}
		else if(obj.command == 'client-call' || obj.command == 'client-accept' || obj.command == 'client-answer' || obj.command == 'client-offer' || obj.command == 'client-candidate')
		{
			if(typeof callbackFunction == 'function')
			{
				if(obj.data.length)
				{
					var i;
					for(i in obj.data)
					{
						data = obj.data[i];
						callbackFunction(data.sender_id, data.receiver_id, obj.command, data);
					}
				}
			}
		}
		else
		{
			if(typeof callbackFunction == 'function')
			{
				if(obj.data.length)
				{
					var i;
					for(i in obj.data)
					{
						data = obj.data[i];
						callbackFunction(data.partner_id, data);
					}
				}
			}
		}
	}
	this.renderMessage = function(message)
	{
		var obj = JSON.parse(message);
	}
}

function planetChat(container, pMessage, websocketURL)
{
	this.container = container;
	this.pMessage = pMessage ;
	this.conn = null;
	this.container = container;
	this.myID = 0;
	this.websocketURL = websocketURL;
	this.connected = false;
	this.firstConnect = true;	
	this.init = function()
	{
	}
	this.connect = function(websocketURL)
	{
		console.log('Connecting...');
		if(!websocketURL)
		{
			websocketURL = this.websocketURL;
		}
		try
		{
			this.conn = new WebSocket(websocketURL);
			this.conn.opopen = function(e){
				console.log('Connected');
				_this.connected = true;
				_this.firstConnect = false;
				clearTimeout(_this.reconnectTimeout);

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
				_this.onOpen(e);
			}
			this.conn.operror = function(e){
				_this.connected = false;
				_this.firstConnect = false;
				_this.onError(e);
				_this.connect();
			}
			this.conn.onclose = function(e){
				_this.connected = false;
				_this.firstConnect = false;
				_this.onClose(e);
				_this.connect();
			}
			this.conn.onmessage = function(e){
				_this.connected = true;
				_this.onMessage(e);
			}
		}
		catch(e)
		{
			console.error(e);
		}
	}
	this.reconnectTimeout = null;
	this.onFirstConnect = function(event)
	{
	};
	this.onReconnect = function(event)
	{
	};
	this.onOpen = function(e)
	{
		console.log(e);
	};
	this.onError = function(e)
	{
		// console.log(e);
	};
	this.onClose = function(e)
	{
		// console.log(e);
	};
	this.onMessage = function(m)
	{
		// console.log(m);
	};
	this.send = function(message)
	{
		try
		{
			if(this.connected)
			{
				this.conn.send(message);
			}
			else
			{
				this.onReconnect = function(event)
				{
					this.conn.send(message);
				};
				this.connect();
			}
		}
		catch(e)
		{
			this.onReconnect = function(event)
			{
				this.conn.send(message);
			};
			this.connect();			
		}

	};
	this.appendChatBox = function(resource)
	{
		$(this.container).append(resource);
	}
	this.openChatBox = function(partner_id)
	{
		$(this.container).find('.planet-chat-box[data-partner-id="'+partner_id+'"]').attr('data-box-open', 'true'); 
		$(this.container).find('.planet-chat-box[data-partner-id="'+partner_id+'"]').find('form input.text-message').select(); 
	}
	this.generateChatBox = function(params)
	{
		var partnerID = params.partner_id;
		var avatar = params.partner_picture || '';
		var html =
		'<div class="planet-chat-box" data-chat-room="'+params.chat_room+'" data-partner-id="'+params.partner_id+'" data-partner-name="'+params.partner_name+'" data-partner-uri="'+params.partner_uri+'" data-partner-picture="'+avatar+'" data-box-open="'+params.box_open+'">\r\n'+
		'  <div class="chat-box-controller">\r\n'+
		'		<div class="chat-unread-message-num" data-notification="0"><div class="chat-unread-message-num-inner"></div></div>\r\n'+
		'		<div class="chat-box-control-exit"><a href="#"><span class="icon remove"></span></a></div>\r\n'+
		'		<div class="chat-box-control-name"><a href="'+params.partner_uri+'">'+params.partner_name+'</a></div>\r\n'+
		'	</div>\r\n'+
		'	<div class="chat-box-wrapper">\r\n'+
		'		<div class="chat-box-header">\r\n'+
		'			<div class="chat-avatar"><a href="'+params.partner_uri+'"><img src="'+avatar+'"></a></div>\r\n'+
		'			<h3><a href="'+params.partner_uri+'">'+params.partner_name+'</a></h3>\r\n'+
		'			<div class="chat-box-icon chat-box-icon-setting">\r\n'+
		'				<a href="#"><span class="icon cog"></span></a>\r\n'+
		'			</div>\r\n'+
		'			<div class="chat-box-menu">\r\n'+
		'				<ul>\r\n'+
		'					<li><a href="#" class="chat-menu-clear-message">'+Language[lang_id].txt_clear_message+'</a></li>\r\n'+
		'					<li><a href="'+params.partner_uri+'" class="chat-menu-view-profile">'+Language[lang_id].txt_view_profile+'</a></li>\r\n'+
		'					<li><a href="#" class="chat-menu-block-user">'+Language[lang_id].txt_block_user+'</a></li>\r\n'+
		'					<li><a href="#" class="chat-menu-minimize-chat">'+Language[lang_id].txt_minimize_chat_box+'</a></li>\r\n'+
		'					<li><a href="#" class="chat-menu-close-chat">'+Language[lang_id].txt_close_chat_box+'</a></li>\r\n'+
		'				</ul>\r\n'+
		'			</div>\r\n'+
		'			<div class="chat-box-icon chat-box-icon-close">\r\n'+
		'				<a href="#"><span class="icon remove"></span></a>\r\n'+
		'			</div>\r\n'+
		'		</div>\r\n'+
		'	  <div class="message-area">\r\n'+
		'			<div class="message-container">\r\n'+
		'					\r\n'+
		'			</div>\r\n'+
		'			<div class="attachment-preview"><div class="image-preview"><ul class="image-list"></ul></div><div class="location-preview"><ul class="geolocation-list"></ul></div></div>\r\n'+
		'		</div>\r\n'+
		'		<div class="chat-footer">\r\n'+
		'			<div class="message-form-inactive">\r\n'+
		'				<div class="chat-waiting">\r\n'+
		'					'+Language[lang_id].msg_waiting_for_connection+' \r\n'+
		'				</div>\r\n'+
		'			</div>\r\n'+
		'			<div class="message-form-active">\r\n'+
		'				<form class="chat-form" action="" method="post">\r\n'+
		'				<div class="form-inner">\r\n'+
		'				<div class="form-submit">\r\n'+
		'				<input type="submit" class="form-send" value="'+Language[lang_id].btn_send+'">\r\n'+
		'				</div>\r\n'+
		'				<div class="form-input">\r\n'+
		'				<input type="text" class="text-message" placeholder="'+Language[lang_id].txt_placeholder_chat+'">\r\n'+
		'				</div>\r\n'+
		'				</div>\r\n'+
		'				<div class="form-features">\r\n'+
		'					<span><a class="chat-video-call" href="#"><span class="icon phone"></span></a></span>\r\n'+
		'					<span><a class="chat-video-call" href="#"><span class="icon video"></span></a></span>\r\n'+
		'					<span><a class="chat-share-image" href="#"><span class="icon image"></span></a></span>\r\n'+
		'					<span><a class="chat-share-location" href="#" data-selector=".planet-chat-box[data-partner-id=&quot;'+params.partner_id+'&quot;] .attachment-preview .location-preview"><span class="icon location"></span></a></span>\r\n'+
		'					<span><a class="post-add-emoji" href="#" data-selector=".post-emoji"><span class="icon emoji"></span></a></span>\r\n'+
		'				</div>\r\n'+
		'				</form>\r\n'+
		'		 	</div>   \r\n'+
		'		</div>\r\n'+
		'	</div>\r\n'+
		'</div>';
		if($(this.container).find('.planet-chat-box[data-partner-id="'+params.partner_id+'"]').length == 0)
		{
			$(this.container).append(html);
		}
	};

	this.showMessage = function(partnerID)
	{
	};
	this.loadMessage = function(partnerID)
	{
		if(partnerID)
		{
			var messageData = {
				'command':'load-message',
				'data':[{
					'partner_id': partnerID,
					'receiver_id': partnerID
				}]
			};
			var messageDataJson = JSON.stringify(messageData);		
			pChat.send(messageDataJson);
		}
	};
	this.sendMarkAsRead = function(partnerID, unreadMessage)
	{
		var messageData = {
				'command':'mark-message',
				'data':[{
					'receiver_id': partnerID,
					'flag': 'read',
					'message_list': unreadMessage
				}]
			};
		var messageDataJson = JSON.stringify(messageData);
		this.conn.send(messageDataJson);	
		setTimeout(function(){
			var unique_id = '';
			var i;
			for(i in unreadMessage)
			{
				unique_id = unreadMessage[i];
				_this.pMessage.unreadMessage[partnerID].splice(this.pMessage.unreadMessage[partnerID].indexOf(unique_id), 1);
				_this.updateUnreadNotification(partnerID);	
			}
		}, markSendDelay);	
	}
	this.receiveMarkAsRead = function(partnerID, unreadMessage)
	{
		var unique_id = '';
		var i;
		for(i in unreadMessage)
		{
			unique_id = unreadMessage[i];
			this.pMessage.unreadMessage[partnerID].splice(this.pMessage.unreadMessage[partnerID].indexOf(unique_id), 1);
			$(this.container).find('.planet-chat-box[data-partner-id="'+partnerID+'"] .message-item[data-unique-id="'+unique_id+'"]').attr('data-read', 'true');
		}
	}
	this.receiveDeleteMessage = function(partnerID, deletedMessage)
	{
		this.markAsDeleted(partnerID, deletedMessage);
	}
	this.markAsDeleted = function(partnerID, deletedMessage)
	{
		var unique_id = '';
		var i;
		for(i in deletedMessage)
		{
			unique_id = deletedMessage[i];
			var messageItem = $(this.container).find('.planet-chat-box[data-partner-id="'+partnerID+'"] .message-item[data-unique-id="'+unique_id+'"]');
			messageItem.attr({'data-deleted':'true'});
			messageItem.find('.message-text').remove();
			messageItem.find('.attachment-list').remove();
			messageItem.find('.message-controller').remove();
			if(messageItem.find('.message-deleted').length)
			{
				messageItem.find('.message-deleted').remove();
			}
			messageItem.append('<div class="message-deleted">'+Language[lang_id].txt_message_deleted+'</div>');
		}
	}
	this.onBeforeSendMessage = function()
	{
	};
	this.onSendMessage = function()
	{
	}
	this.onAfterSendMessage = function()
	{
	};
	this.updateUnreadNotification = function(partnerID)
	{
		// Add notif
		var chatBox = $(this.container).find('.planet-chat-box[data-partner-id="'+partnerID+'"]');
		chatBox.find('.chat-unread-message-num').attr('data-notification', this.pMessage.unreadMessage[partnerID].length);		
		chatBox.find('.chat-unread-message-num-inner').text(this.pMessage.unreadMessage[partnerID].length);	
		setTimeout(function(){
			chatBox.find('.chat-unread-message-num').removeClass('bounce');
		}, bounceDuration);	
		chatBox.find('.chat-unread-message-num').addClass('bounce');
	};
	this.playSoundIfClosed = function(partnerID)
	{
		// Add notif
		var chatBox = $(this.container).find('.planet-chat-box[data-partner-id="'+partnerID+'"]');
		var sm = chatBox.attr('data-box-open');
		if(sm == 'false' && this.pMessage.unreadMessage[partnerID].length > 0)
		{
		}
	};
	this.prepareFileUploader = function()
	{
		$(this.container).append('<div style="position:absolute;left:-10000px;top:-10000px;"><input type="file" class="chat-image-browser" accept="image/*"></div>');
		$(this.container).find('input[type="file"].chat-image-browser').on('change', function(evt){
			var selector = $(evt.target).parent().attr('data-selector');
			handleFileSelectPrivate(evt, selector);
		});
	};
	this.browseFile = function(selector){
		$(this.container).find('input[type="file"].chat-image-browser').parent().attr('data-selector', selector);
		$(this.container).find('input[type="file"].chat-image-browser').click();
	};
	var _this = this;
	this.init();
	
}

var pMessage = new PlanetMessage();
var pChat = new planetChat('.planet-chat-container', pMessage, websocketURL);

pChat.onOpen = function(e){
	console.log("Connection established!");	 
	$(pChat.container).attr('data-connected', 'true');   
	pChat.connected = true;
};
pChat.onError = function(e) {
	console.error("WebSocket error observed:", e);
	$(pChat.container).attr('data-connected', 'false');   
	pChat.connected = false;
};
pChat.onClose = function(e) {
	$(pChat.container).attr('data-connected', 'false');   
	pChat.connected = false;
};
pChat.onMessage = function(e){
	pChat.pMessage.addMessage(e.data, 
		{
			'send-message':function(partner_id, data){
				var currentTime = new Date();
				var messageTime = Math.round((data.timestamp - currentTime.getTime())/1000);
				var phpTimestamp = Math.round((data.timestamp)/1000);
				var uniqueID = data.unique_id;
				var textTime = timeToText(messageTime);
				
				var postTime = '<span class="post-time" title="'+data.date_time+'" data-timestamp="'+phpTimestamp+'"><span>'+textTime+'</span></span>';
						
				var message = '';
				var attahment = renderAttachment(data);
				if(data.by_system)
				{
					message =
					'        	<div class="message-item clearfix from-system" data-unique-id="'+uniqueID+'" data-timestamp="'+data.timestamp+'">\r\n'+
					'            	<div class="message-time">'+postTime+'</div>\r\n'+
					'            	<div class="message-text">'+generateSystemMessage(data)+'</div>\r\n'+
					'            	'+attahment+'\r\n'+
					'            	<div class="message-controller clearfix-top">\r\n'+
					'                	<span><a href="#" class="delete-message">'+Language[lang_id].btn_delete+'</a></span>\r\n'+
					'                </div>\r\n'+
					'            </div>\r\n'
				}
				else
				{
					if(data.partner_id == data.sender_id)
					{
						message =
						'        	<div class="message-item clearfix from-partner" data-unique-id="'+uniqueID+'" data-timestamp="'+data.timestamp+'">\r\n'+
						'            	<div class="message-sender">'+data.sender_name+'</div>\r\n'+
						'            	<div class="message-time">'+postTime+'</div>\r\n'+
						'            	<div class="message-text">'+data.message.text+'</div>\r\n'+
						'            	'+attahment+'\r\n'+
						'            	<div class="message-controller clearfix-top">\r\n'+
						'                	<span><a href="#" class="delete-message">'+Language[lang_id].btn_delete+'</a></span>\r\n'+
						'                </div>\r\n'+
						'            </div>\r\n'
					}
					else
					{
						var read = data.read?'true':'false';
						message =
						'        	<div class="message-item clearfix from-me" data-unique-id="'+uniqueID+'" data-read="'+read+'" data-timestamp="'+data.timestamp+'">\r\n'+
						'            	<div class="message-sender">'+data.sender_name+'</div>\r\n'+
						'            	<div class="message-time">'+postTime+'</div>\r\n'+
						'            	<div class="message-text">'+data.message.text+'</div>\r\n'+
						'            	'+attahment+'\r\n'+
						'            	<div class="message-controller clearfix-top">\r\n'+
						'                	<span><a href="#" class="delete-message-for-all">'+Language[lang_id].btn_delete_for_all+'</a></span>\r\n'+
						'                	<span><a href="#" class="delete-message">'+Language[lang_id].btn_delete_for_me+'</a></span>\r\n'+
						'                </div>\r\n'+
						'            </div>\r\n'
					}
				}
				var messageBox = $(pChat.container).find('.planet-chat-box[data-partner-id="'+data.partner_id+'"] .message-container');
				if(messageBox.length != 0)
				{
					if(messageBox.find('[data-unique-id="'+uniqueID+'"]').length == 0)
					{
						messageBox.append(message);
					}
				}
				else
				{
					var params = {partner_id:data.partner_id,partner_name:data.partner_name,partner_uri:data.partner_uri,partner_picture:data.avatar,box_open:'false'};
					pChat.generateChatBox(params);
					messageBox = $(pChat.container).find('.planet-chat-box[data-partner-id="'+data.partner_id+'"] .message-container');
					if(messageBox.find('[data-unique-id="'+uniqueID+'"]').length == 0)
					{
						messageBox.append(message);
					}
				}
				if(messageBox.find('.message-item').length > 0)
				{
					$(messageBox).parent().scrollTop(messageBox.parent().prop('scrollHeight'));
				}
				if(data.read == false)
				{
					if(data.receiver_id == pChat.myID)
					{
						if(typeof pChat.pMessage.unreadMessage == 'undefined')
						{
							pChat.pMessage.unreadMessage = {};
						}
						if(typeof pChat.pMessage.unreadMessage[data.partner_id] == 'undefined')
						{
							pChat.pMessage.unreadMessage[data.partner_id] = [];
						}
						if(pChat.pMessage.unreadMessage[data.partner_id].indexOf(data.unique_id) == -1)
						{
							pChat.pMessage.unreadMessage[data.partner_id].push(data.unique_id);
						}
					}					
				}
				pChat.updateUnreadNotification(data.partner_id);
				pChat.playSoundIfClosed(data.partner_id);
			},
			'delete-message-for-all':function(partner_id, data){
				pChat.receiveDeleteMessage(partner_id, data.message_list);
			},
			'mark-message':function(partner_id, data){
				pChat.receiveMarkAsRead(partner_id, data.message_list);
			},
			'log-in':function(my_id, data){
				pChat.connected = true;
				pChat.myID = my_id;
				if($(pChat.container).find('.planet-chat-box').length > 0)
				{
					$(pChat.container).attr('data-connected', 'true');
					loadMessage();
				}
				if($(pChat.container).find('.planet-chat-box').length == 0)
				{
					setTimeout(function(){
						loadMessage();
					}, reconnectDelay);
				}
				$('.user-list li').each(function(index, element) {
					var username = $(this).attr('data-username');
					if(username == pChat.myID)
					{
						$(this).remove();
					}
				});
				console.log('Connected');
			},
			'video-call':function(partner_id, data){
				clearTimeout(callIddleTimeout);
				clearTimeout(missedCallTimeout);
				if(data.receiver_id == pChat.myID)
				{
					if(callStatus == 'IDDLE')
					{
						candidatePartnerID = data.partner_id;
						var avatar = data.avatar || '';
						var html = '<div class="caller-avatar"><img src="'+avatar+'"></div>\r\n'+
							'<div class="caller-name">'+data.partner_name+'</div>\r\n'+
							'<div class="call-type">'+Language[lang_id].txt_video_call+'</div>\r\n'+
							'<div class="call-action">\r\n'+
							'<span class="call-accept video-call-accept"><a href="#" data-partner-id="'+candidatePartnerID+'"><span></span></a></span>\r\n'+
							'<span class="call-reject video-call-reject"><a href="#" data-partner-id="'+candidatePartnerID+'"><span></span></a></span>\r\n'+
							'</div>';
						$('.video-call-popup').empty().append(
							html
							);
						$('.planet-video-call').css({'display':'block'});
						try
						{
							clearInterval(ringtoneInterval);
							ringtone.pause();
						}
						catch(e)
						{
						}
						ringtone.play();
						ringtoneInterval = setInterval(function(){
							ringtone.play();
						}, repeatRingtoneDelay);
						missedCallTimeout = setTimeout(function(){
							callStatus = 'IDDLE';
							try
							{
								var message = JSON.stringify({command:'missed-call', data:[{sender_id:data.sender_id, receiver_id:data.receiver_id, chat_room:data.chat_room}]});
								pChat.send(message);
								$('.planet-video-call').css({'display':'none'});
								clearInterval(ringtoneInterval);
								ringtone.pause();
							}
							catch(e)
							{
							}
						}, delayToMissedCall);
					}
				}
				else
				{
				}
			},
			'voice-call':function(partner_id, data){
				clearTimeout(callIddleTimeout);
				clearTimeout(missedCallTimeout);
				if(data.receiver_id == pChat.myID)
				{
					if(callStatus == 'IDDLE')
					{
						candidatePartnerID = data.partner_id;
						var avatar = data.avatar || '';
						var html = '<div class="caller-avatar"><img src="'+avatar+'"></div>\r\n'+
							'<div class="caller-name">'+data.partner_name+'</div>\r\n'+
							'<div class="call-type">'+Language[lang_id].txt_voice_call+'</div>\r\n'+
							'<div class="call-action">\r\n'+
							'<span class="call-accept voice-call-accept"><a href="#" data-partner-id="'+candidatePartnerID+'"><span></span></a></span>\r\n'+
							'<span class="call-reject voice-call-reject"><a href="#" data-partner-id="'+candidatePartnerID+'"><span></span></a></span>\r\n'+
							'</div>';
						$('.video-call-popup').empty().append(
							html
							);
						$('.planet-video-call').css({'display':'block'});
						try
						{
							clearInterval(ringtoneInterval);
							ringtone.pause();
						}
						catch(e)
						{
						}
						ringtone.play();
						ringtoneInterval = setInterval(function(){
							ringtone.play();
						}, repeatRingtoneDelay);
						missedCallTimeout = setTimeout(function(){
							callStatus = 'IDDLE';
							try
							{
								var message = JSON.stringify({command:'missed-call', data:[{sender_id:data.sender_id, receiver_id:data.receiver_id, chat_room:data.chat_room}]});
								pChat.send(message);
								$('.planet-video-call').css({'display':'none'});
								clearInterval(ringtoneInterval);
								ringtone.pause();
							}
							catch(e)
							{
							}
						}, delayToMissedCall);
					}
				}
				else
				{
				}
			},
			'on-call':function(partner_id, data){
				clearTimeout(callIddleTimeout);
				callStatus = 'ONCALL';
				try
				{
					$('.planet-video-call').css({'display':'none'});
					clearInterval(ringtoneInterval);
					ringtone.pause();
				}
				catch(e)
				{
				}
			},
			'call-iddle':function(partner_id, data){
				callIddleTimeout = setTimeout(function(){
				callStatus = 'IDDLE';
				}, callIddleDelay);
				try
				{
					$('.planet-video-call').css({'display':'none'});
					clearInterval(ringtoneInterval);
					ringtone.pause();
				}
				catch(e)
				{
				}
			},
			'reject-call':function(partner_id, data){
				clearInterval(missedcallInterval);
				hideProgressBar();
				callIddleTimeout = setTimeout(function(){
				callStatus = 'IDDLE';
				}, callIddleDelay);
				try
				{
					$('.planet-video-call').css({'display':'none'});
					clearInterval(ringtoneInterval);
					ringtone.pause();
				}
				catch(e)
				{
				}
			},
			'user-on-system':function(partner_id, data){	
				for (var [username, userData] of Object.entries(data.users)) {
					if(username != pChat.myID)
					{
						html = buildUserResource(username, userData);
						if($('.user-list li[data-username="'+username+'"]').length == 0)
						{
							$('.user-list').prepend(html);
						}
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
			},			
			'client-call':function(sender_id, receiver_id, command, data){
			},
			'client-accept':function(sender_id, receiver_id, command, data){
				processWebRTCInfo(sender_id, receiver_id, command, data) 	
			},
			'client-answer':function(sender_id, receiver_id, command, data){
				processWebRTCInfo(sender_id, receiver_id, command, data) 	
			},
			'client-offer':function(sender_id, receiver_id, command, data){
				processWebRTCInfo(sender_id, receiver_id, command, data) 	
			},
			'client-candidate':function(sender_id, receiver_id, command, data){
				processWebRTCInfo(sender_id, receiver_id, command, data) 	
			}
		}
	);	   
};
var answer = 0;
var callSuccess = 0;

function processWebRTCInfo(sender_id, receiver_id, command, data) 
{
	console.log(command);
	console.log(data);
	delete data.sender_id;
	delete data.receiver_id;
	delete data.partner_id;
    switch (command) {
        case 'client-call':
        case 'client-accept':
			if(localStream != null)
			{
				onICECandidate(localStream, sender_id, receiver_id);
				pc.createOffer({
					offerToReceiveAudio: 1,
					offerToReceiveVideo: 1
				}).then(function(desc) {
					pc.setLocalDescription(desc).then(
						function() {
							var data2 = JSON.parse(JSON.stringify(pc.localDescription));
							if(sender_id == pChat.myID)
							{
								data2.receiver_id = receiver_id;
							}
							else
							{
								data2.receiver_id = sender_id;
							}
							var message = JSON.stringify({
								'command': 'client-offer',
								'data': [data2]
							});
							pChat.send(message);
						}
					).catch(function(e) {
						console.log("Problem with publishing client offer" + e);
					});
				}).catch(function(e) {
					console.log("Problem while doing client-call: " + e);
				});
			}
            break;
        case 'client-answer':
            if (pc == null) 
			{
                console.error('Before processing the client-answer, I need a client-offer');
                break;
            }
			delete data.read;
			delete data.receiver_name;
			delete data.date_time;
			delete data.sender_name;
			delete data.timestamp;
			delete data.unique_id;
            pc.setRemoteDescription(new RTCSessionDescription(data), 
				function() {
				
				},
                function(e) 
				{
                    console.log("Problem while doing client-answer: ", e);
                }
            );
            break;
        case 'client-offer':
           	onICECandidate(localStream, sender_id, receiver_id);
            pc.setRemoteDescription(new RTCSessionDescription(data), function() 
			{
                if (answer == 0) 
				{
                    pc.createAnswer(function(desc) 
					{
                        pc.setLocalDescription(desc, function() 
						{
							var data2 = JSON.parse(JSON.stringify(pc.localDescription));
							data2.receiver_id = receiver_id;
							var message = JSON.stringify({
								'command': 'client-answer',
								'data': [data2]
							});
							pChat.send(message);

                       }, 
						function(e) 
						{
                            console.log("Problem getting client answer: ", e);
                        });
                    }, 
					function(e) 
					{
                        console.log("Problem while doing client-offer: ", e);
                    });
                    answer = 1;
                }
            }, 
			function(e) 
			{
                console.log("Problem while doing client-offer: ", e);
            });
            break;
        case 'client-candidate':
            if (pc == null) 
			{
                console.error('Before processing the client-answer, I need a client-offer');
                break;
            }
			pc.addIceCandidate(new RTCIceCandidate(data),
				function() {
				
				},
				function(e) 
				{
					console.log("Problem adding ice candidate: " + e);
				}
			);
		break;
    }
};

function sendOnCallSignal(partner_id, chat_room) 
{
	if(onCall)
	{
		var data = {
			'command': 'on-call',
			data: [{
				receiver_id: partner_id,
				chat_room: chat_room
			}]
		};
		socket.send(JSON.stringify(data));
	}
	else
	{
		clearInterval(sendOnCallInterval);
	}
}


function onICECandidate(localStream, sender_id, receiver_id) 
{
    pc = new RTCPeerConnection(configuration);
    pc.onicecandidate = function(event) {
        if (event.candidate) 
		{
			var data2 = JSON.parse(JSON.stringify(event.candidate));
			if(sender_id == pChat.pMessage.myID)
			{
				data2.receiver_id = receiver_id;
			}
			else
			{
				data2.receiver_id = sender_id;
			}
			var message = JSON.stringify({
				'command': 'client-candidate',
				'data': [data2]
			});
			pChat.send(message);
        }
    };
    try 
	{
        pc.addStream(localStream);
    } 
	catch (e) 
	{
        var tracks = localStream.getTracks();
        for (var i = 0; i < tracks.length; i++) 
		{
            pc.addTrack(tracks[i], localStream);
        }
    }
    pc.ontrack = function(e) {
        callSuccess++;
        document.querySelector('.video-area').setAttribute('data-mode', '1');
        remoteVideo.srcObject = e.streams[0];
		clearInterval(sendOnCallInterval);
		clearTimeout(missedcallTimeout);
		hideProgressBar();
		sendOnCallInterval = setInterval(function(){
			sendOnCallSignal(receiver_id, '');
		}, sendOnCallDelay);

        dimensionChanged();
    };
	pc.oniceconnectionstatechange = function(e)
	{
		if(e.target.iceConnectionState == 'disconnected')
		{
			endCall();
		}
		else if(e.target.iceConnectionState == 'connected')
		{
			onCall = true;
		}
	};
	pc.onicegatheringstatechange = function(e)
	{
		if(e.target.iceConnectionState == 'disconnected')
		{
			endCall();
		}
		else if(e.target.iceConnectionState == 'connected')
		{
			onCall = true;
		}
	}
}
var onCall = false;
function buildUserResource(username, userData)
{
	var html = '<li class="user-item user-item-'+userData.sex+'" data-username="'+username+'" data-name="'+userData.full_name+'"><a href="#'+username+'" class="link-to-chat" data-partner-id="'+username+'" data-username="'+username+'" data-partner-name="'+userData.full_name+'" data-partner-uri="'+username+'" data-partner-picture="'+userData.avatar+'"><div class="user-item-container"><div class="avatar"><img src="'+userData.avatar+'"></div><div class="full-name">'+userData['full_name']+'</div><div class="username">'+username+'</div></div></a></li>';
	return html;
}

var clientWidth = function () {  return Math.max(window.innerWidth, document.documentElement.clientWidth);};
var clientHeight = function () {  return Math.max(window.innerHeight, document.documentElement.clientHeight);};


function loadMessage()
{
	$(pChat.container).find('.planet-chat-box').each(function(index, element) {
		var partner_id = $(this).closest('.planet-chat-box').attr('data-partner-id');
		var messageData = {
			'command':'load-message',
			'data':[{
				'partner_id': partner_id,
				'receiver_id': partner_id
			}]
		}
		var messageDataJson = JSON.stringify(messageData);			
		pChat.send(messageDataJson);
	});
}
function generateSystemMessage(data)
{
	var message = '';
	if(data.message.text == 'video-call' || data.message.text == 'voice-call')
	{
		if(data.sender_id == pChat.myID)
		{
			message = sprintf(Language[lang_id].txt_you_call, data.partner_name)
		}
		else
		{
			message = sprintf(Language[lang_id].txt_call_you, data.partner_name)
		}
	}
	return message;
}
function sendFeedback()
{
	var chatBox;
	var sm;
	if(tabFocus)
	{
		for (const [partnerID, unreadMessage] of Object.entries(pChat.pMessage.unreadMessage)) 
		{
			chatBox = $('.planet-chat-box[data-partner-id="'+partnerID+'"]');
			sm = chatBox.attr('data-box-open');
			if(sm == 'true')
			{ 
				if(unreadMessage.length)
				{
					pChat.sendMarkAsRead(partnerID, unreadMessage);
				}
			}
		}
	}
	if(pChat.connected)
	{
		$(pChat.container).attr('data-connected', 'true');
	}
	else
	{
		$(pChat.container).attr('data-connected', 'false');
	}
	setTimeout(function(){
		window.requestAnimationFrame(sendFeedback);
	}, feedbackRetryDelay);
}


window.requestAnimationFrame(sendFeedback);

function renderAttachment(data)
{
	var html = '';
	if(typeof data.message.attachment != 'undefined')
	{
		if(data.message.attachment.length)
		{
			html += '<div class="attachment-list">\r\n';
			var i, j, k;
			for(i in data.message.attachment)
			{
				k = data.message.attachment[i].type;
				if(k.indexOf('image') != -1)
				{
					j = data.message.attachment[i].url;
					html += '<div class="chat-attachment"><img src="'+j+'"></div>\r\n';
				}
				if(k == 'geolocation')
				{
					var position = {};
					var content = data.message.attachment[i].content || {};
					if(typeof content == 'string')
					{
						position = JSON.parse(content);
					}
					else
					{
						position = content;
					}
					html += '<div class="chat-attachment"><div class="navidate-to-point"><a href="https://maps.google.com/maps?daddr='+position.latitude+','+position.longitude+'" target="_blank"><span></span></a></div>'+renderLocation(position.latitude, position.longitude)+'</div>\r\r';
				}
			}
			html += '</div>\r\n';
		}
	}
	return html;
};
function getChatAttachmen(box)
{
	var attachment = [];
	if($(box).find('.image-list .attachment-item').length)
	{
		$(box).find('.attachment-item').each(function(index, element) {
			var url = $(this).find('img.attachment-content-image').attr('src');
			var id = $(this).attr('data-id') || '';
			var type = $(this).attr('data-type') || '';
			if(id.indexOf('image') == -1 && id != '')
			{
        		attachment.push({id:id, url:url, type:type});
			}
        });
	}
	if($(box).find('.geolocation-list .geolocation-item').length)
	{
		$(box).find('.geolocation-item').each(function(index, element) {
			var id = $(this).attr('data-id') || '';
			var type = $(this).attr('data-type') || '';
			var content = $(this).attr('data-content') || '{}';
			var geo = JSON.parse(content);
			if(id.indexOf('image') == -1 && id != '')
			{
        		attachment.push({id:id, content:geo, type:type});
			}
        });
	}
	return attachment;
}

function submitChatForm(frm)
{
	var box = $(frm).closest('.planet-chat-box');
	var message = box.find('.text-message').val();
	var attachment = getChatAttachmen(box);
	message = message.trim();
	if(message.length > 0)
	{
		var partner_id = box.attr('data-partner-id');
		var messageData = {
			'command':'send-message',
			'data':[{
				'partner_id': partner_id,
				'receiver_id': partner_id,
				'message': {
					'text':message,
					'attachment':attachment
				}
			}]
		}
		var messageDataJson = JSON.stringify(messageData);	
		pChat.send(messageDataJson);
		box.find('.text-message').val('');
		var i, j;
		for(i in attachment)
		{
			j = attachment[i].id;
			$(box).find('.attachment-preview [data-id="'+j+'"]').remove();
		}
	}
}
/*
MD5
By Chris Coyier
*/
var MD5=function(a){function b(a,b){return a<<b|a>>>32-b}function c(a,b){var c,d,e,f,g;return e=2147483648&a,f=2147483648&b,c=1073741824&a,d=1073741824&b,g=(1073741823&a)+(1073741823&b),c&d?2147483648^g^e^f:c|d?1073741824&g?3221225472^g^e^f:1073741824^g^e^f:g^e^f}function d(a,b,c){return a&b|~a&c}function e(a,b,c){return a&c|b&~c}function f(a,b,c){return a^b^c}function g(a,b,c){return b^(a|~c)}function h(a,e,f,g,h,i,j){return a=c(a,c(c(d(e,f,g),h),j)),c(b(a,i),e)}function i(a,d,f,g,h,i,j){return a=c(a,c(c(e(d,f,g),h),j)),c(b(a,i),d)}function j(a,d,e,g,h,i,j){return a=c(a,c(c(f(d,e,g),h),j)),c(b(a,i),d)}function k(a,d,e,f,h,i,j){return a=c(a,c(c(g(d,e,f),h),j)),c(b(a,i),d)}function l(a){for(var b,c=a.length,d=c+8,e=(d-d%64)/64,f=16*(e+1),g=Array(f-1),h=0,i=0;i<c;)b=(i-i%4)/4,h=i%4*8,g[b]=g[b]|a.charCodeAt(i)<<h,i++;return b=(i-i%4)/4,h=i%4*8,g[b]=g[b]|128<<h,g[f-2]=c<<3,g[f-1]=c>>>29,g}function m(a){var d,e,b="",c="";for(e=0;e<=3;e++)d=a>>>8*e&255,c="0"+d.toString(16),b+=c.substr(c.length-2,2);return b}function n(a){a=a.replace(/\r\n/g,"\n");for(var b="",c=0;c<a.length;c++){var d=a.charCodeAt(c);d<128?b+=String.fromCharCode(d):d>127&&d<2048?(b+=String.fromCharCode(d>>6|192),b+=String.fromCharCode(63&d|128)):(b+=String.fromCharCode(d>>12|224),b+=String.fromCharCode(d>>6&63|128),b+=String.fromCharCode(63&d|128))}return b}var p,q,r,s,t,u,v,w,x,o=Array(),y=7,z=12,A=17,B=22,C=5,D=9,E=14,F=20,G=4,H=11,I=16,J=23,K=6,L=10,M=15,N=21;for(a=n(a),o=l(a),u=1732584193,v=4023233417,w=2562383102,x=271733878,p=0;p<o.length;p+=16)q=u,r=v,s=w,t=x,u=h(u,v,w,x,o[p+0],y,3614090360),x=h(x,u,v,w,o[p+1],z,3905402710),w=h(w,x,u,v,o[p+2],A,606105819),v=h(v,w,x,u,o[p+3],B,3250441966),u=h(u,v,w,x,o[p+4],y,4118548399),x=h(x,u,v,w,o[p+5],z,1200080426),w=h(w,x,u,v,o[p+6],A,2821735955),v=h(v,w,x,u,o[p+7],B,4249261313),u=h(u,v,w,x,o[p+8],y,1770035416),x=h(x,u,v,w,o[p+9],z,2336552879),w=h(w,x,u,v,o[p+10],A,4294925233),v=h(v,w,x,u,o[p+11],B,2304563134),u=h(u,v,w,x,o[p+12],y,1804603682),x=h(x,u,v,w,o[p+13],z,4254626195),w=h(w,x,u,v,o[p+14],A,2792965006),v=h(v,w,x,u,o[p+15],B,1236535329),u=i(u,v,w,x,o[p+1],C,4129170786),x=i(x,u,v,w,o[p+6],D,3225465664),w=i(w,x,u,v,o[p+11],E,643717713),v=i(v,w,x,u,o[p+0],F,3921069994),u=i(u,v,w,x,o[p+5],C,3593408605),x=i(x,u,v,w,o[p+10],D,38016083),w=i(w,x,u,v,o[p+15],E,3634488961),v=i(v,w,x,u,o[p+4],F,3889429448),u=i(u,v,w,x,o[p+9],C,568446438),x=i(x,u,v,w,o[p+14],D,3275163606),w=i(w,x,u,v,o[p+3],E,4107603335),v=i(v,w,x,u,o[p+8],F,1163531501),u=i(u,v,w,x,o[p+13],C,2850285829),x=i(x,u,v,w,o[p+2],D,4243563512),w=i(w,x,u,v,o[p+7],E,1735328473),v=i(v,w,x,u,o[p+12],F,2368359562),u=j(u,v,w,x,o[p+5],G,4294588738),x=j(x,u,v,w,o[p+8],H,2272392833),w=j(w,x,u,v,o[p+11],I,1839030562),v=j(v,w,x,u,o[p+14],J,4259657740),u=j(u,v,w,x,o[p+1],G,2763975236),x=j(x,u,v,w,o[p+4],H,1272893353),w=j(w,x,u,v,o[p+7],I,4139469664),v=j(v,w,x,u,o[p+10],J,3200236656),u=j(u,v,w,x,o[p+13],G,681279174),x=j(x,u,v,w,o[p+0],H,3936430074),w=j(w,x,u,v,o[p+3],I,3572445317),v=j(v,w,x,u,o[p+6],J,76029189),u=j(u,v,w,x,o[p+9],G,3654602809),x=j(x,u,v,w,o[p+12],H,3873151461),w=j(w,x,u,v,o[p+15],I,530742520),v=j(v,w,x,u,o[p+2],J,3299628645),u=k(u,v,w,x,o[p+0],K,4096336452),x=k(x,u,v,w,o[p+7],L,1126891415),w=k(w,x,u,v,o[p+14],M,2878612391),v=k(v,w,x,u,o[p+5],N,4237533241),u=k(u,v,w,x,o[p+12],K,1700485571),x=k(x,u,v,w,o[p+3],L,2399980690),w=k(w,x,u,v,o[p+10],M,4293915773),v=k(v,w,x,u,o[p+1],N,2240044497),u=k(u,v,w,x,o[p+8],K,1873313359),x=k(x,u,v,w,o[p+15],L,4264355552),w=k(w,x,u,v,o[p+6],M,2734768916),v=k(v,w,x,u,o[p+13],N,1309151649),u=k(u,v,w,x,o[p+4],K,4149444226),x=k(x,u,v,w,o[p+11],L,3174756917),w=k(w,x,u,v,o[p+2],M,718787259),v=k(v,w,x,u,o[p+9],N,3951481745),u=c(u,q),v=c(v,r),w=c(w,s),x=c(x,t);return(m(u)+m(v)+m(w)+m(x)).toLowerCase()};

var chatMenuTimeout = setTimeout(function(){}, 100);
$(document).ready(function(e) {
	var metaContent = $('head meta[name="member-from-id"]').attr('content') || '';
	if(metaContent != '')
	{
		localStorageName += ('_'+metaContent);
	}
	var currentChat = unserializePartner('.planet-chat-container', localStorageName);
	if(currentChat.length)
	{
		var i;
		for(i in currentChat)
		{
			pChat.generateChatBox(currentChat[i]);
			if(currentChat[i].box_open == 'true')
			{
				var partner_id = $(this).closest('.planet-chat-box').attr('data-partner-id');
				pChat.loadMessage(partner_id);
			}
		}
	}
	$(document).on('submit', '.planet-chat-box form', function(e){
		e.preventDefault();
		submitChatForm($(this));
	});
	$(document).on('click', '.delete-message-for-all', function(e){
		var message_item = $(this).closest('.message-item');
		var message_box = $(this).closest('.planet-chat-box');
		var unique_id = message_item.attr('data-unique-id');
		var partner_id = message_box.attr('data-partner-id');
		if(confirm(Language[lang_id].msg_confirm_delete_message)){
			var messageData = {
				'command':'delete-message-for-all',
				'data':[{
					'receiver_id': partner_id,
					'message_list': [unique_id]
				}]
			}
			var messageDataJson = JSON.stringify(messageData);			
			pChat.send(messageDataJson);
			pChat.markAsDeleted(partner_id, [unique_id]);
		}
		e.preventDefault();
	});
	$(document).on('click', '.delete-message', function(e){
		var message_item = $(this).closest('.message-item');
		var message_box = $(this).closest('.planet-chat-box');
		var unique_id = message_item.attr('data-unique-id');
		var partner_id = message_box.attr('data-partner-id');
		if(confirm(Language[lang_id].msg_confirm_delete_message))
		{
			var messageData = {
				'command':'delete-message',
				'data':[{
					'receiver_id': partner_id,
					'message_list': [unique_id]
				}]
			};
			var messageDataJson = JSON.stringify(messageData);			
			pChat.send(messageDataJson);
			pChat.markAsDeleted(partner_id, [unique_id]);
		}
		e.preventDefault();
	});

    $(document).on('click', '.chat-box-control-name a', function(e){		
		$(this).closest('.planet-chat-box').attr('data-box-open', 'true');
		var partner_id = $(this).closest('.planet-chat-box').attr('data-partner-id');
		serializePartner('.planet-chat-container', localStorageName);
		$(this).closest('.planet-chat-box').find('form input.text-message').select()
		pChat.loadMessage(partner_id);
		e.preventDefault();
	});

    $(document).on('click', '.chat-box-control-exit a', function(e){
		$(this).closest('.planet-chat-box').remove();
		serializePartner('.planet-chat-container', localStorageName)
		e.preventDefault();
	});

    $(document).on('click', '.chat-box-header h3 a, .chat-box-header a.chat-menu-minimize-chat, .chat-box-icon-close a', function(e){
		$(this).closest('.planet-chat-box').attr('data-box-open', 'false');
		serializePartner('.planet-chat-container', localStorageName)
		e.preventDefault();
	});
    $(document).on('click', '.chat-box-header a.chat-menu-close-chat', function(e){
		$(this).closest('.planet-chat-box').remove();
		serializePartner('.planet-chat-container', localStorageName)
		e.preventDefault();
	});
	
    $(document).on('click', '.chat-box-icon-setting a', function(e){
		$(this).closest('.planet-chat-box').siblings().each(function(index, element) {
           $(this).attr('data-box-menu', 'false'); 
        });
		var stat = $(this).closest('.planet-chat-box').attr('data-box-menu') || '';
		if(stat == 'true')
		{
			$(this).closest('.planet-chat-box').attr('data-box-menu', 'false');
		}
		else
		{
			$(this).closest('.planet-chat-box').attr('data-box-menu', 'true');
		}
		e.preventDefault();
	});
	$(document).on('mouseout', '.chat-box-icon-setting a, .chat-box-menu', function(e){
		var chatBox = $(this).closest('.planet-chat-box');
		var mn = chatBox.attr('data-box-menu') || '';
		if(mn == 'true')
		{
			clearTimeout(chatMenuTimeout);
			chatMenuTimeout = setTimeout(function(){
				chatBox.attr('data-box-menu', 'false');
			}, hideChatMenuDelay);
		}
	});
	$(document).on('mouseover', '.chat-box-icon-setting a, .chat-box-menu', function(e){
		clearTimeout(chatMenuTimeout);
	});

    $(document).on('click', '.chat-box-header a.chat-menu-clear-message', function(e){
		var chatBox = $(this).closest('.planet-chat-box');
		var partner_id = chatBox.attr('data-partner-id');
		chatBox.attr('data-box-menu', 'false');
		chatBox.attr('data-box-menu', 'false');
		var partner_id = chatBox.attr('data-partner-id');
		if(confirm(Language[lang_id].msg_confirm_clear_all_message))
		{
			var messageData = {
				'command':'clear-message',
				'data':[{
					'partner_id': partner_id
				}]
			};
			var messageDataJson = JSON.stringify(messageData);			
			pChat.send(messageDataJson);
			chatBox.find('.message-container').empty();
		}
		e.preventDefault();
	});

    $(document).on('click', '.chat-box-header a.chat-menu-block-user', function(e){
		$(this).closest('.planet-chat-box').attr('data-box-menu', 'false');
		var partner_id = $(this).closest('.planet-chat-box').attr('data-partner-id');
		if(confirm(Language[lang_id].txt_clear_message))
		{
		}
		e.preventDefault();
	});
	$(document).on('click', '.link-to-chat', function(e){
		var ww = clientWidth();
		var wh = clientHeight();
		if(ww < 200 || wh < 200)
		{
			window.location = $(this).attr('href');
		}
		else
		{
		var chatBox = $(this);
		var partner_id = chatBox.attr('data-partner-id');
		var partner_name = chatBox.attr('data-partner-name');
		var partner_uri = chatBox.attr('data-partner-uri');
		var partner_picture = chatBox.attr('data-partner-picture');
		var box_open = 'true';
		var chat_room = '';
		var params = {partner_id:partner_id,partner_name:partner_name,partner_uri:partner_uri,partner_picture:partner_picture,box_open:box_open,chat_room:chat_room};
		var documentWidth = parseInt($(document).width()) - 32;
		var maxNumBox = Math.floor(documentWidth/chatBoxWidth);
		var cuurentNumBox = $(pChat.container).find('.planet-chat-box').length;
		
		if($(pChat.container).find('.planet-chat-box[data-partner-id="'+partner_id+'"]').length > 0)
		{
			pChat.openChatBox(partner_id);
		}
		else
		{
			if(cuurentNumBox >= maxNumBox)
			{
				$(pChat.container).find('.planet-chat-box[data-box-open="false"]').filter(':first').remove();
				cuurentNumBox = $(pChat.container).find('.planet-chat-box').length;
			}
			while(cuurentNumBox >= maxNumBox)
			{
				$(pChat.container).find('.planet-chat-box:first-child').remove();
				cuurentNumBox = $(pChat.container).find('.planet-chat-box').length;
			}
			var html = pChat.generateChatBox(params);
			pChat.appendChatBox(html);
			pChat.openChatBox(partner_id);
			pChat.loadMessage(partner_id);
		}
		serializePartner('.planet-chat-container', localStorageName)		
		e.preventDefault();
		}
	});
	
	$(document).on('click', '.chat-share-image', function(e){
	});
	$(document).on('click', '.call-reject a', function(e){
		e.preventDefault();
		try
		{
			var message = JSON.stringify({command:'reject-call', data:[{sender_id:pChat.myID, receiver_id:candidatePartnerID, chat_room:candidateChatRoom}]});
			pChat.send(message);
			ringtone.pause();
		}
		catch(e2)
		{
		}
		clearTimeout(ringtoneInterval);
		$('.planet-video-call').css({'display':'none'});
	});
	$(document).on('paste', '.text-message', function(e){
		var selector = $(this).closest('.planet-chat-box').find(".attachment-preview .image-preview ul");
		if(!e.clipboardData)
		{
			e = e.originalEvent;
		}
		handlePasteImageChat(e, selector);
    });
	$(document).on('click', '.chat-share-location', function(e){
		var selector = $(this).attr('data-selector') || '';
		var url = 'lib.ajax/ajax-show-map.php?private=1&selector='+encodeURIComponent(selector);
		$('.geolocation-dialog').attr({'data-location-async':url, 'data-title':Language[lang_id].txt_your_location}).html('');
		mui.showPopUp('geolocation-dialog');
		e.preventDefault();
	});
	$(document).on('click', '.chat-share-image', function(e){
		var box = $(this).closest('.planet-chat-box');
		var partner_id = box.attr('data-partner-id');
		pChat.browseFile('.planet-chat-box[data-partner-id="'+partner_id+'"] .image-preview ul.image-list');
		e.preventDefault();
	});
	$(document).on('click', '.chat-video-call', function(e){
		var box = $(this).closest('.planet-chat-box');
		var partner_id = box.attr('data-partner-id');
		var message = JSON.stringify({
			'command':'video-call',
			'data':[{'receiver_id':partner_id}]
		});

		clearTimeout(missedcallTimeout);
		clearInterval(missedcallInterval);
		showProgressBar();
		var countDown = delayToMissedCall;
		missedcallInterval = setInterval(function(){
			countDown -= 1000;
			var pcnt = 100*(countDown/delayToMissedCall);
			pcnt = pcnt.toFixed(2);
			if(pcnt <= 0)
			{
				clearInterval(missedcallInterval);
				hideProgressBar();
			}
			$('.progress-bar-inner').css('width', pcnt+'%');
		}, 1000);

		pChat.send(message);
		startVideoCall(partner_id, '', false);
		e.preventDefault();
	});
	
	$(document).on('click', '.video-call-accept a', function(e){
		var receiver_id = $(this).attr('data-partner-id');
		clearTimeout(ringtoneInterval);
		clearTimeout(missedcallTimeout);
		clearInterval(missedcallInterval);
		startVideoCall(receiver_id, '', true);
		$('.planet-video-call').css({'display':'none'});
		e.preventDefault();
	});
	
	
	pChat.prepareFileUploader();
	pChat.connect();
	
});
var mediaConstrains = {video:true, audio:true};

function startVideoCall(partner_id, chat_room, asCallee) 
{
	$('.control-area').css({'display':'block'});
	
	
    localVideo = document.getElementById('localVideo');
    remoteVideo = document.getElementById('remoteVideo');
    navigator.mediaDevices.getUserMedia(mediaConstrains).then(function(stream) 
	{
        localVideo.srcObject = stream;
        localStream = stream;
        // Go show myself
        localVideo.addEventListener('loadedmetadata',
            function() {
				if(asCallee)
				{
					var message = JSON.stringify({
						'command':'client-accept', 
							'data':[{
								'receiver_id' : partner_id,
								'chat_room' : chat_room
							}]}
						);
					pChat.send(message);
				}
				else
				{
					var message = JSON.stringify({
						'command':'client-call', 
							'data':[{
								'receiver_id' : partner_id,
								'chat_room' : chat_room
							}]}
						);
					pChat.send(message);
				}
				//
				if(asCallee)
				{
					console.log('as callee');
					var data = {'receiver_id':pChat.myID};
					var command = 'client-call';
					var sender_id = partner_id;
					processWebRTCInfo(sender_id, pChat.myID, command, data) 
				}
				
            }
        );

    }).catch(function(e) 
	{
        console.log("Problem while getting audio video stuff ", e);
    });

    localVideo.addEventListener('click', function(e) 
	{
        var mode = document.querySelector('.video-area').getAttribute('data-mode');
        if (mode == '1') 
		{
            document.querySelector('.video-area').setAttribute('data-mode', '2');
            dimensionChanged();
        }
    });
    remoteVideo.addEventListener('click', function(e) 
	{
        var mode = document.querySelector('.video-area').getAttribute('data-mode');
        if (mode == '2') 
		{
            document.querySelector('.video-area').setAttribute('data-mode', '1');
            dimensionChanged();
        }
    });

    var ww = 0;
    var wh = 0;
    var lw = 0;
    var lh = 0;
    var rw = 0;
    var rh = 0;
    setInterval(function() {
        if (ww != window.innerWidth) 
		{
            ww = window.innerWidth;
            dimensionChanged();
        }
        if (wh != window.innerHeight) 
		{
            wh = window.innerHeight;
            dimensionChanged();
        }
        if (lw != localVideo.videoWidth) 
		{
            lw = localVideo.videoWidth;
            dimensionChanged();
        }
        if (lh != localVideo.videoHeight) 
		{
            lh = localVideo.videoHeight;
            dimensionChanged();
        }
        if (rw != remoteVideo.videoWidth) 
		{
            rw = remoteVideo.videoWidth;
            dimensionChanged();
        }
        if (rh != remoteVideo.videoHeight) 
		{
            rh = remoteVideo.videoHeight;
            dimensionChanged();
        }
    }, 100);
}

function dimensionChanged() 
{
    var maxHeight = window.innerHeight;
    var maxWidth = Math.min(window.innerWidth - 250, 1000);
    var mode = document.querySelector('.video-area').getAttribute('data-mode');
    var largeVideoWidth = maxWidth;
    var smallVideoWidth = maxWidth * 0.2;

    var screenRatio = maxWidth / maxHeight;
    var remoteVideoRatio = (remoteVideo.videoWidth / remoteVideo.videoHeight);
    var localVideoRatio = (localVideo.videoWidth / localVideo.videoHeight);

    remoteVideo.style.opacity = 0;
    localVideo.style.opacity = 0;
    remoteVideo.style.width = 'auto';
    remoteVideo.style.height = 'auto';
    localVideo.style.width = 'auto';
    localVideo.style.height = 'auto';

    if (mode == '1') 
	{
        if (remoteVideoRatio > screenRatio) 
		{
            // Use maxWidth
            if (remoteVideo.videoWidth > largeVideoWidth) 
			{
                remoteVideo.style.width = largeVideoWidth + 'px';
                remoteVideo.style.height = 'auto';
            } 
			else 
			{
                remoteVideo.style.width = largeVideoWidth + 'px';
                remoteVideo.style.height = 'auto';
            }
        } 
		else 
		{
            // Use maxHeight
            var vw = parseInt((maxHeight / remoteVideoRatio));
            var vh = maxHeight;
            if (vw > largeVideoWidth) 
			{
                vw = largeVideoWidth;
                vh = vw / remoteVideoRatio;
            }
            if (vh > maxHeight) 
			{
                vh = maxHeight;
                vw = vh * remoteVideoRatio;
            }
            remoteVideo.style.height = vh + 'px';
            remoteVideo.style.width = vw + 'px';
        }

        localVideo.style.width = parseInt(smallVideoWidth) + 'px';
        localVideo.style.height = parseInt((smallVideoWidth / localVideoRatio)) + 'px';
    }
    if (mode == '2') 
	{
        if (localVideoRatio > screenRatio) 
		{
            // Use maxWidth
            if (localVideo.videoWidth > largeVideoWidth) 
			{
                localVideo.style.width = largeVideoWidth + 'px';
                localVideo.style.height = 'auto';
            } 
			else 
			{
                localVideo.style.width = largeVideoWidth + 'px';
                localVideo.style.height = 'auto';
            }
        } 
		else 
		{
            // Use maxHeight
            localVideo.style.height = maxHeight + 'px';
            localVideo.style.width = parseInt((maxHeight / localVideoRatio)) + 'px';
        }
        remoteVideo.style.width = parseInt(smallVideoWidth) + 'px';
        remoteVideo.style.height = parseInt((smallVideoWidth / remoteVideoRatio)) + 'px';;
    }
    remoteVideo.style.opacity = 1;
    localVideo.style.opacity = 1;
}

var missedcallInterval = null;
var missedcallTimeout = null;

function showProgressBar()
{
	$('.progress-bar-container').css('display', 'block');
}
function hideProgressBar()
{
	$('.progress-bar-container').css('display', 'none');
}

function pauseVideo()
{
	var obj = $('.pause-video')[0];
	if(obj.className.indexOf('paused') > -1)
	{
		obj.className = 'pause-video';
		if(localStream != null)
		localStream.getTracks()[1].enabled = true;
	}
	else
	{
		obj.className = 'pause-video paused';
		if(localStream != null)
		localStream.getTracks()[1].enabled = false;
	}
}
function pauseAudio()
{
	var obj = $('.pause-audio')[0];
	if(obj.className.indexOf('paused') > -1)
	{
		obj.className = 'pause-audio';
		if(localStream != null)
		localStream.getTracks()[0].enabled = true;
	}
	else
	{
		obj.className = 'pause-audio paused';
		if(localStream != null)
		localStream.getTracks()[0].enabled = false;
	}
}



function serializePartner(selector, key)
{
	var qc = [];
	$(selector).find('.planet-chat-box').each(function(index, element) {
        var chatBox = $(this);
		var partner_id = chatBox.attr('data-partner-id');
		var partner_name = chatBox.attr('data-partner-name');
		var partner_uri = chatBox.attr('data-partner-uri');
		var partner_picture = chatBox.attr('data-partner-picture');
		var box_open = chatBox.attr('data-box-open');
		var chat_room = chatBox.attr('data-chat-room');
		qc.push({partner_id:partner_id,partner_name:partner_name,partner_uri:partner_uri,partner_picture:partner_picture,box_open:box_open,chat_room:chat_room});
    });
	var qcstr = JSON.stringify(qc);
	window.localStorage.setItem(key, qcstr);
}
function unserializePartner(selector, key)
{
	var qcstr = window.localStorage.getItem(key) || '[]';
	return JSON.parse(qcstr);
}









/////////////////////////////











function handleFileSelectPrivate(evt, selector)
{
	var box = $(selector).closest('.planet-chat-box');
	var receiver_id = box.attr('data-parent-id');
	var files = evt.target.files;
	var randid = 0;
	var i, j = 0, f;
	var date = new Date();
	now = date.getTime();
	var f;
	var canvasid = '';
	for(i = 0, j = 0; (f = files[i]); i++)
	{
		if (f.type.match('image.*'))
		{
		randid = Math.round((now+Math.random()*1000000));
		canvasid = 'canvas'+randid;
		var postid = $(selector).attr('data-post-id') || '0';
		var img_360 = $('head meta[name="image-360"]').attr('content');
		var name = f.name || '';
		$(' <li class="attachment-item" data-id="image'+randid+'" data-waiting="true"> <div class="attachment-item-container attachment-send-loading"><div class="image-upload-progress"><div class="image-upload-progress-inner"></div></div><a href="#" class="attachment-remover"><span class="icon remove"></span></a><canvas class="attachment-content-image" id="'+canvasid+'">'+Language[lang_id].txt_not_supported+'</canvas></div></li> ').appendTo(selector);
		var formdata = new FormData();
		formdata.append('images', f);
		formdata.append('name', name);
		$.ajax({
			
			xhr: function(){
				if(window.XMLHttpRequest)
				{
					var xhr = new window.XMLHttpRequest();
				}
				else
				{
					var xhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xhr.upload.addEventListener("progress", function(evt){
					if (evt.lengthComputable){
						var percentComplete = evt.loaded / evt.total;
						percentComplete = (percentComplete * 100);
						$('[data-id="image'+randid+'"] .image-upload-progress-inner').css({'width':percentComplete+'%'});
						percentComplete = parseInt(percentComplete);
						if (percentComplete === 100){
							$('[data-id="image'+randid+'"] .image-upload-progress').hide(50);
						}
					
					}
				}, false);
				
				return xhr;
			},			
			
			
			url:'lib.ajax/ajax-upload-image-private.php?private=1&data_id=image'+randid+'&receiver_id='+receiver_id,
			type:'POST',
			data:formdata,
			dataType:'html',
			processData:false,
			contentType:false,
			success:function(answer){
				var obj = $('<div>'+answer+'</div>');
				var data_id = obj.find('li.attachment-item').attr('data-original-id') || '';
				var original_name = obj.find('li.attachment-item').attr('data-original-name') || '';
				if(data_id != '')
				{
					$('li[data-id="'+data_id+'"]').replaceWith(answer);
				}
			}
		});
		var reader = new FileReader();
		reader.onload = (function(theFile)
		{
			return function(e) 
			{
				 loadImage(canvasid, e.target.result);
			};
		})(f);
		reader.readAsDataURL(f);
		j++;
		}
	}
}






function handlePasteImageChat(e, selector)
{
	if (e && e.clipboardData && e.clipboardData.getData) 
	{
		if((/Files/.test(e.clipboardData.types) && !/text\/html/.test(e.clipboardData.types))) {
			// Paste image from other application
			var blob = e.clipboardData.items[0].getAsFile();
			var reader = new window.FileReader();
			reader.onloadend = function(){
				var randid = Math.round((Math.random()*100000000));
				var canvasid = 'canvas'+randid;
				var receiver_id = $(selector).closest('.planet-chat-box').attr('data-partner-id') || '0';
				var md5_original = MD5($.base64.decode(reader.result));
				if(checkMD5(md5_original, selector))
				{
					$(' <li class="attachment-item" data-hash="'+md5_original+'" data-id="image'+randid+'" data-waiting="true"> <div class="attachment-item-container attachment-send-loading"><div class="image-upload-progress"><div class="image-upload-progress-inner"></div></div><a href="#" class="attachment-remover"><span class="icon remove"></span></a><canvas class="attachment-content-image" id="'+canvasid+'">'+Language[lang_id].txt_not_supported+'</canvas></div></li> ').appendTo(selector);
					$.ajax({
						xhr: function(){
							if(window.XMLHttpRequest)
							{
								var xhr = new window.XMLHttpRequest();
							}
							else
							{
								var xhr = new ActiveXObject("Microsoft.XMLHTTP");
							}
							xhr.upload.addEventListener("progress", function(evt){
								if (evt.lengthComputable){
									var percentComplete = evt.loaded / evt.total;
									percentComplete = (percentComplete * 100);
									$('[data-id="image'+randid+'"] .image-upload-progress-inner').css({'width':percentComplete+'%'});
									percentComplete = parseInt(percentComplete);
									if (percentComplete === 100){
										$('[data-id="image'+randid+'"] .image-upload-progress').hide(50);
									}
								
								}
							}, false);
							
							return xhr;
						},
						url:'lib.ajax/ajax-upload-image-private.php?private=1&data_id=image'+randid+'&receiver_id='+receiver_id,
						type:'POST',
						data:{base64data:reader.result,md5_original:md5_original},
						dataType:'html',
						success:function(answer){
							var obj = $('<div>'+answer+'</div>');
							var data_id = obj.find('li.attachment-item').attr('data-original-id') || '';
							if(data_id != '')
							{
								$(selector).find('li[data-id="'+data_id+'"]').replaceWith(answer);
							}
						},
						error:function(error){
							console.error(error);
						}
					});
					loadImage(canvasid, reader.result);
				}
				else
				{
				}
			}
			reader.readAsDataURL(blob); 
			if(e.preventDefault)
			{
				e.stopPropagation();
				e.preventDefault();
			}
		}
		else if(/Files/.test(e.clipboardData.types) && /text\/html/.test(e.clipboardData.types))
		{
			// Paste image from web application
			var html = e.clipboardData.getData('text/html');
			var container = document.createElement('div');
			container.innerHTML = html;
			for(var i in container.childNodes)
			{
				if(container.childNodes.item(i).tagName == 'IMG' || container.childNodes.item(i).tagName == 'img')
				{
					var src = container.childNodes.item(i).getAttribute('src');
					var randid = Math.round((Math.random()*100000000));
					var canvasid = 'canvas'+randid;
					var receiver_id = $(selector).closest('.planet-chat-box').attr('data-partner-id') || '0';
					var md5_original = MD5(src);
					if(checkMD5(md5_original, selector))
					{
						$(' <li class="attachment-item" data-hash="'+md5_original+'" data-id="image'+randid+'" data-waiting="true"> <div class="attachment-item-container attachment-send-loading"><div class="image-upload-progress"><div class="image-upload-progress-inner"></div></div><a href="#" class="attachment-remover"><span class="icon remove"></span></a><canvas class="attachment-content-image" id="'+canvasid+'">'+Language[lang_id].txt_not_supported+'</canvas></div></li> ').appendTo(selector);
						$.ajax({
							xhr: function(){
								if(window.XMLHttpRequest)
								{
									var xhr = new window.XMLHttpRequest();
								}
								else
								{
									var xhr = new ActiveXObject("Microsoft.XMLHTTP");
								}
								xhr.upload.addEventListener("progress", function(evt){
									if (evt.lengthComputable){
										var percentComplete = evt.loaded / evt.total;
										percentComplete = (percentComplete * 100);
										$('[data-id="image'+randid+'"] .image-upload-progress-inner').css({'width':percentComplete+'%'});
										percentComplete = parseInt(percentComplete);
										if (percentComplete === 100){
											$('[data-id="image'+randid+'"] .image-upload-progress').hide(50);
										}
									
									}
								}, false);
								
								return xhr;
							},
							url:'lib.ajax/ajax-upload-image-private.php?private=1&data_id=image'+randid+'&receiver_id='+receiver_id,
							type:'POST',
							data:{externalimagedata:src,md5_original:md5_original},
							dataType:'html',
							success:function(answer){
								var obj = $('<div>'+answer+'</div>');
								var data_id = obj.find('li.attachment-item').attr('data-original-id') || '';
								if(data_id != '')
								{
									$(selector).find('li[data-id="'+data_id+'"]').replaceWith(answer);
								}
							},
							error:function(error){
								console.error(error);
							}
						});
						loadImage(canvasid, src);
					}
					else
					{
					}
				}
			}
			if(e.preventDefault)
			{
				e.stopPropagation();
				e.preventDefault();
			}
		}
		else if(/text\/html/.test(e.clipboardData.types)) 
		{
			// convert HTML to text
			var data = e.clipboardData.getData('text/html');
			try{
			e.clipboardData.setData('text/plain', data);
			if(e.preventDefault)
			{
				e.stopPropagation();
				e.preventDefault();
			}
			}
			catch(e){}
		}
		else if(/text\/plain/.test(e.clipboardData.types)) 
		{
			// do nothing
		}
		else 
		{
		}
	}
	else 
	{
	}
}


function timeToText(tm)
{
	tm = Math.abs(tm);
	
	tm = parseInt(tm);
	var text = '';
	var dt = new Date();
	var fulltime = date('j F Y H:i:s', ((dt.getTime()/1000)-tm));
	if(tm<1)
	{
		text = '<span>'+Language[lang_id].txt_now+'</span>';
	}
	else if(tm>=1 && tm<60)
	{
		text = '<span>'+Language[lang_id].txt_just_now+'</span>';
	}
	else if(tm>=60 && tm<3600)
	{
		var ni = Math.floor(tm/60);
		if(ni>1)
		{
			text = '<span>'+ ni
			+ ' '+Language[lang_id].txt_left_minute2+'</span>';
		}
		else
		{
			text = '<span>'+ ni
			+ ' '+Language[lang_id].txt_left_minute+'</span>';
		}
	}
	else if(tm>=3600 && tm<86400)
	{
		var nh = Math.floor(tm/3600);
		if(nh>1)
		{
			text = '<span title="'+fulltime+'">'+nh 
			+ ' '+Language[lang_id].txt_left_hour2+'</span>';
		}
		else
		{
			text = '<span title="'+fulltime+'">'+nh 
			+ ' '+Language[lang_id].txt_left_hour+'</span>';
		}
	}
	else if(tm>=86400 && tm<2592000)
	{
		var nd = Math.floor(tm/86400);
		if(nd>1)
		{
			text = '<span title="'+fulltime+'">'+nd 
			+ ' '+Language[lang_id].txt_left_day2+'</span>';
		}
		else
		{
			text = '<span title="'+fulltime+'">'+nd 
			+ ' '+Language[lang_id].txt_left_day+'</span>';
		}
	}
	else if(tm>=2592000 && tm<31536000)
	{
		var nm = Math.floor(tm/2592000);
		if(nm>1)
		{
			text = '<span title="'+fulltime+'">'+nm 
			+ ' '+Language[lang_id].txt_left_month2+'</span>';
		}
		else
		{
			text = '<span title="'+fulltime+'">'+nm 
			+ ' '+Language[lang_id].txt_left_month+'</span>';
		}
	}
	else
	{
		var ny = Math.floor(tm/31536000); 
		if(ny>1)
		{
			text = '<span title="'+fulltime+'">'+ ny
			+ ' '+Language[lang_id].txt_left_year2+'';
		}
		else
		{
			text = '<span title="'+fulltime+'">'+ ny
			+ ' '+Language[lang_id].txt_left_year+'';
		}
	}
	return text;
}

function date(format, timestamp) {
  //  discuss at: http://phpjs.org/functions/date/
  // original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
  // original by: gettimeofday
  //    parts by: Peter-Paul Koch (http://www.quirksmode.org/js/beat.html)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: MeEtc (http://yass.meetcweb.com)
  // improved by: Brad Touesnard
  // improved by: Tim Wiel
  // improved by: Bryan Elliott
  // improved by: David Randall
  // improved by: Theriault
  // improved by: Theriault
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Theriault
  // improved by: Thomas Beaucourt (http://www.webapp.fr)
  // improved by: JT
  // improved by: Theriault
  // improved by: RafaÅ‚ Kukawski (http://blog.kukawski.pl)
  // improved by: Theriault
  //    input by: Brett Zamir (http://brett-zamir.me)
  //    input by: majak
  //    input by: Alex
  //    input by: Martin
  //    input by: Alex Wilson
  //    input by: Haravikk
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: majak
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: omid (http://phpjs.org/functions/380:380#comment_137122)
  // bugfixed by: Chris (http://www.devotis.nl/)
  //        note: Uses global: php_js to store the default timezone
  //        note: Although the function potentially allows timezone info (see notes), it currently does not set
  //        note: per a timezone specified by date_default_timezone_set(). Implementers might use
  //        note: this.php_js.currentTimezoneOffset and this.php_js.currentTimezoneDST set by that function
  //        note: in order to adjust the dates in this function (or our other date functions!) accordingly
  //   example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400);
  //   returns 1: '09:09:40 m is month'
  //   example 2: date('F j, Y, g:i a', 1062462400);
  //   returns 2: 'September 2, 2003, 2:26 am'
  //   example 3: date('Y W o', 1062462400);
  //   returns 3: '2003 36 2003'
  //   example 4: x = date('Y m d', (new Date()).getTime()/1000);
  //   example 4: (x+'').length == 10 // 2009 01 09
  //   returns 4: true
  //   example 5: date('W', 1104534000);
  //   returns 5: '53'
  //   example 6: date('B t', 1104534000);
  //   returns 6: '999 31'
  //   example 7: date('W U', 1293750000.82); // 2010-12-31
  //   returns 7: '52 1293750000'
  //   example 8: date('W', 1293836400); // 2011-01-01
  //   returns 8: '52'
  //   example 9: date('W Y-m-d', 1293974054); // 2011-01-02
  //   returns 9: '52 2011-01-02'

  var that = this;
  var jsdate, f;
  // Keep this here (works, but for code commented-out below for file size reasons)
  // var tal= [];
  var txt_words = [
    Language[lang_id].txt_day_sun, Language[lang_id].txt_day_mon, Language[lang_id].txt_day_tue, 
	Language[lang_id].txt_day_web, Language[lang_id].txt_day_thu, Language[lang_id].txt_day_fri, 
	Language[lang_id].txt_day_sat,
    Language[lang_id].txt_month_january, Language[lang_id].txt_month_february, Language[lang_id].txt_month_march, 
	Language[lang_id].txt_month_april, Language[lang_id].txt_month_may, Language[lang_id].txt_month_june,
    Language[lang_id].txt_month_july, Language[lang_id].txt_month_august, Language[lang_id].txt_month_september, 
	Language[lang_id].txt_month_october, Language[lang_id].txt_month_november, Language[lang_id].txt_month_december
  ];
  // trailing backslash -> (dropped)
  // a backslash followed by any character (including backslash) -> the character
  // empty string -> empty string
  var formatChr = /\\?(.?)/gi;
  var formatChrCb = function(t, s) {
    return f[t] ? f[t]() : s;
  };
  var _pad = function(n, c) {
    n = String(n);
    while (n.length < c) {
      n = '0' + n;
    }
    return n;
  };
  f = {
    // Day
    d: function() { // Day of month w/leading 0; 01..31
      return _pad(f.j(), 2);
    },
    D: function() { // Shorthand day name; Mon...Sun
      return f.l()
        .slice(0, 3);
    },
    j: function() { // Day of month; 1..31
      return jsdate.getDate();
    },
    l: function() { // Full day name; Monday...Sunday
      return txt_words[f.w()] + 'day';
    },
    N: function() { // ISO-8601 day of week; 1[Mon]..7[Sun]
      return f.w() || 7;
    },
    S: function() { // Ordinal suffix for day of month; st, nd, rd, th
      var j = f.j();
      var i = j % 10;
      if (i <= 3 && parseInt((j % 100) / 10, 10) == 1) {
        i = 0;
      }
      return ['st', 'nd', 'rd'][i - 1] || 'th';
    },
    w: function() { // Day of week; 0[Sun]..6[Sat]
      return jsdate.getDay();
    },
    z: function() { // Day of year; 0..365
      var a = new Date(f.Y(), f.n() - 1, f.j());
      var b = new Date(f.Y(), 0, 1);
      return Math.round((a - b) / 864e5);
    },

    // Week
    W: function() { // ISO-8601 week number
      var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3);
      var b = new Date(a.getFullYear(), 0, 4);
      return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
    },

    // Month
    F: function() { // Full month name; January...December
      return txt_words[6 + f.n()];
    },
    m: function() { // Month w/leading 0; 01...12
      return _pad(f.n(), 2);
    },
    M: function() { // Shorthand month name; Jan...Dec
      return f.F()
        .slice(0, 3);
    },
    n: function() { // Month; 1...12
      return jsdate.getMonth() + 1;
    },
    t: function() { // Days in month; 28...31
      return (new Date(f.Y(), f.n(), 0))
        .getDate();
    },

    // Year
    L: function() { // Is leap year?; 0 or 1
      var j = f.Y();
      return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
    },
    o: function() { // ISO-8601 year
      var n = f.n();
      var W = f.W();
      var Y = f.Y();
      return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
    },
    Y: function() { // Full year; e.g. 1980...2010
      return jsdate.getFullYear();
    },
    y: function() { // Last two digits of year; 00...99
      return f.Y()
        .toString()
        .slice(-2);
    },

    // Time
    a: function() { // am or pm
      return jsdate.getHours() > 11 ? 'pm' : 'am';
    },
    A: function() { // AM or PM
      return f.a()
        .toUpperCase();
    },
    B: function() { // Swatch Internet time; 000..999
      var H = jsdate.getUTCHours() * 36e2;
      // Hours
      var i = jsdate.getUTCMinutes() * 60;
      // Minutes
      var s = jsdate.getUTCSeconds(); // Seconds
      return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
    },
    g: function() { // 12-Hours; 1..12
      return f.G() % 12 || 12;
    },
    G: function() { // 24-Hours; 0..23
      return jsdate.getHours();
    },
    h: function() { // 12-Hours w/leading 0; 01..12
      return _pad(f.g(), 2);
    },
    H: function() { // 24-Hours w/leading 0; 00..23
      return _pad(f.G(), 2);
    },
    i: function() { // Minutes w/leading 0; 00..59
      return _pad(jsdate.getMinutes(), 2);
    },
    s: function() { // Seconds w/leading 0; 00..59
      return _pad(jsdate.getSeconds(), 2);
    },
    u: function() { // Microseconds; 000000-999000
      return _pad(jsdate.getMilliseconds() * 1000, 6);
    },

    // Timezone
    e: function() { // Timezone identifier; e.g. Atlantic/Azores, ...
      // The following works, but requires inclusion of the very large
      // timezone_abbreviations_list() function.
      /*              return that.date_default_timezone_get();
       */
      throw Language[lang_id].msg_not_suppoted_date_tz_php;
    },
    I: function() { // DST observed?; 0 or 1
      // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
      // If they are not equal, then DST is observed.
      var a = new Date(f.Y(), 0);
      // Jan 1
      var c = Date.UTC(f.Y(), 0);
      // Jan 1 UTC
      var b = new Date(f.Y(), 6);
      // Jul 1
      var d = Date.UTC(f.Y(), 6); // Jul 1 UTC
      return ((a - c) !== (b - d)) ? 1 : 0;
    },
    O: function() { // Difference to GMT in hour format; e.g. +0200
      var tzo = jsdate.getTimezoneOffset();
      var a = Math.abs(tzo);
      return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
    },
    P: function() { // Difference to GMT w/colon; e.g. +02:00
      var O = f.O();
      return (O.substr(0, 3) + ':' + O.substr(3, 2));
    },
    T: function() { // Timezone abbreviation; e.g. EST, MDT, ...
      // The following works, but requires inclusion of the very
      // large timezone_abbreviations_list() function.
      /*              var abbr, i, os, _default;
      if (!tal.length) {
        tal = that.timezone_abbreviations_list();
      }
      if (that.php_js && that.php_js.default_timezone) {
        _default = that.php_js.default_timezone;
        for (abbr in tal) {
          for (i = 0; i < tal[abbr].length; i++) {
            if (tal[abbr][i].timezone_id === _default) {
              return abbr.toUpperCase();
            }
          }
        }
      }
      for (abbr in tal) {
        for (i = 0; i < tal[abbr].length; i++) {
          os = -jsdate.getTimezoneOffset() * 60;
          if (tal[abbr][i].offset === os) {
            return abbr.toUpperCase();
          }
        }
      }
      */
      return 'UTC';
    },
    Z: function() { // Timezone offset in seconds (-43200...50400)
      return -jsdate.getTimezoneOffset() * 60;
    },

    // Full Date/Time
    c: function() { // ISO-8601 date.
      return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
    },
    r: function() { // RFC 2822
      return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
    },
    U: function() { // Seconds since UNIX epoch
      return jsdate / 1000 | 0;
    }
  };
  this.date = function(format, timestamp) {
    that = this;
    jsdate = (timestamp === undefined ? new Date() : // Not provided
      (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
      new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
    );
    return format.replace(formatChr, formatChrCb);
  };
  return this.date(format, timestamp);
}

