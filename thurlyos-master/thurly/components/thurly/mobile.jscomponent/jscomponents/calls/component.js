/**
* @bxjs_lang_path component.php
*/
BX.listeners = {};

// region Constants

var Sound = {
	incoming: "incoming",
	startCall: "startcall"
};

var TelephonyUiState = {
	INCOMING: "INCOMING",
	FINISHED: "FINISHED",
	STARTED: "STARTED",
	OUTGOING: "OUTGOING",
	WAITING: "WAITING",
	CALLBACK: "CALLBACK"
};

var TelephonyUiEvent = {
	onHangup: "onHangupCallClicked",
	onSpeakerphoneChanged: "onSpeakerphoneCallClicked",
	onMuteChanged: "onMuteCallClicked",
	onPauseChanged: "onPauseCallClicked",
	onNumpadClicked: "onNumpadClicked",
	onFormFolded: "onFoldCallClicked",
	onFormExpanded: "onUnfoldIconClicked",
	onCloseClicked: "onCloseCallClicked",
	onSkipClicked: "onSkipCallClicked",
	onAnswerClicked: "onAnswerCallClicked",
	onNumpadClosed: "onNumpadClosed",
	onNumpadButtonClicked: "onNumpadButtonClicked",
	onPhoneNumberReceived: "onPhoneNumberReceived",
	onContactListChoose: "onContactListChoose",
	onContactListMenuChoose: "onContactListMenuChoose"
};

var CallEvent = {
	Connected: "onCallConnected",
	Disconnected: "onCallDisconnected",
	Failed: "onCallFailed",
	ProgressToneStart: "onProgressToneStart",
	ProgressToneStop: "onProgressToneStop"
};

var CallState = {
	CONNECTED: "connected",
	DISCONNECTED: "disconnected",
	CONNECTING: "connecting"
};

var eventTimeRange = 30;
var crmPathTemplate = "mobile/crm/#ENTITY#/?page=view&#ENTITY#_id=#ID#";
var entityTypes = ["lead", "contact", "company", "deal"];

// endregion Constants

// region Common functions
function timesUp(timestamp)
{
	return ((Math.abs((new Date()).getTime() - timestamp)) >= eventTimeRange * 1000);
}

function preparePush(push)
{
	if (typeof (push) !== 'object' || typeof (push.params) === 'undefined')
	{
		return {'ACTION': 'NONE'};
	}

	let result = {};
	try {
		result = JSON.parse(push.params);
	}
	catch (e)
	{
		result = {'ACTION': push.params};
	}

	return result;
}

function getCrmShowPath(entityType, entityId)
{
	entityType = entityType.toLowerCase();
	if(entityTypes.indexOf(entityType) === -1)
		return "";

	return currentDomain + BX.componentParameters.get("siteDir", "/") +  crmPathTemplate.replace(/#ENTITY#/g, entityType).replace(/#ID#/g, entityId);
}

function decodeHtml(input)
{
    return input.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&nbsp;/g, ' ');
}

// endregion Common functions

// region Mobile Webrtc

if(typeof calls == "undefined")
{
	include("Calls");
	var calls = new WebRTC();
}

var callsModuleWrapper = function ()
{

};

callsModuleWrapper.prototype =
    {
        UI: {
            state: {
                "OUTGOING_CALL": "outgoing_call",
                "INCOMING_CALL": "incoming_call",
                "CONVERSATION": "conversation",
                "FAIL_CALL": "fail_call"
            },
            show: function (state, options)
            {
                var params = options || {};
                params.state = state;
                calls.showUi(params);

            },
            close: function (params)
            {
                calls.closeUi(params);
            },
            showLocalVideo: function (params)
            {
                calls.showLocalVideo(params);
            }
        },
        createPeerConnection: function (params)
        {
            calls.createPeerConnection();
        },
        createOffer: function (params)
        {
            calls.createOffer();
        },
        createAnswer: function (params)
        {
            calls.createAnswer(params);
        },
        addIceCandidates:function (params)
        {
            calls.addIceCandidates(params);
        },
        setRemoteDescription:function (params)
        {
            calls.setRemoteDescription(params);
        },
        getUserMedia:function (params)
        {
            calls.getUserMedia(params);
        },
        onReconnect:function (params)
        {
            calls.onReconnect(params);
        },
        setEventListeners:function (params)
        {
            calls.setEventListeners(params);
        }
    };

var webrtc = new callsModuleWrapper();

MobileWebrtc = function ()
{
    this.siteDir = (typeof mobileSiteDir == "undefined" ? "/" : mobileSiteDir);
    this.initiator = false;
    this.userId = BX.componentParameters.get('userId', 0);
    this.callUserId = 0;
    this.debug = false;
    this.incomingCallTimeOut = 2;
    this.delayedIncomingCall = {};
    this.incomingCallTimeOutId = null;
    this.callChatId = 0;

    this.waitTimeout = false;
    this.callGroupUsers = [];
    this.pcStart = {};
    this.connected = {};
    this.sessionDescription = {};
    this.remoteSessionDescription = {};
    this.iceCandidates = [];
    this.iceCandidatesToSend = [];
    this.iceCandidateTimeout = 0;
    this.userData = {};
    this.redialParameters = {
		userId: 0,
		video: false
	};

	this.ready = false;
	this.callInit = false;
	this.callActive = false;
	this.callToGroup = false;
	this.peerConnectionInited = false;

	this.init();
};

MobileWebrtc.prototype.init = function ()
{
	BX.addCustomEvent("onCallInvite", e =>
	{
		console.log("onCallInvite: ", e);
		if(e['userData'])
		{
			this.appendUserData(e['userData']);
		}
		this.startCall(e.userId, e.video);
	});

	this.attachListeners();
	BX.addCustomEvent("onPullEvent-im", this.onPullEvent.bind(this));
	BX.addCustomEvent("onAppActive", this.onAppActive.bind(this));
	this.onAppActive();
};

MobileWebrtc.prototype.onAppActive = function ()
{
	let push = preparePush(Application.getLastNotification());
	if (push.TAG)
	{
		push.ACTION = push.TAG;
	}
	if (push.ACTION && push.ACTION.substr(0, 6) === 'IMINV_' && push.PARAMS)
	{
		console.log('Starting with PUSH: ', push);
		this.appendUserData(push.PARAMS.users, push.PARAMS.hrphoto);

		let pushParams = push.ACTION.split("_");
		let userId = parseInt(pushParams[1]);
		let callTime = parseInt(pushParams[2]);
		let video = ((pushParams.length >=4) && pushParams[3] === "Y");

		let callParams = push.PARAMS;

		if(!timesUp(callTime*1000))
		{
			//setTimeout(() => this.startCall(userId, video), 1500);
			this.onPullCommandInvite(callParams);
		}
	}
};

MobileWebrtc.prototype.signalingLink = function() {
    return currentDomain + '/mobile/ajax.php?mobile_action=calls&'
};

MobileWebrtc.prototype.attachListeners = function ()
{
    calls.setEventListeners(
        {
            //UI callbacks
            "onAnswer": this.logged("onUiAnswer", this.onUiAnswer),
            "onDecline": this.logged("onUiDecline", this.onUiDecline),
            "onCallback": this.logged("onUiCallback", this.onUiCallback),
            "onClose": this.logged("onUiClose", this.onUiClose),
            //WebRTC callbacks
            "onUserMediaSuccess": this.logged("_onUserMediaSuccess", this._onUserMediaSuccess),
            "onDisconnect": this.logged("_onDisconnect", this._onDisconnect),
            "onPeerConnectionCreated": this.logged("_onPeerConnectionCreated", this._onPeerConnectionCreated),
            "onIceCandidateDiscovered": this.logged("_onIceCandidateDiscovered", this._onIceCandidateDiscovered),
            "onLocalSessionDescriptionCreated": this.logged("_onLocalSessionDescriptionCreated", this._onLocalSessionDescriptionCreated),
            "onIceConnectionStateChanged": this.logged("_onIceConnectionStateChanged", this._onIceConnectionStateChanged),
            "onIceGatheringStateChanged": this.logged("_onIceGatheringStateChanged", this._onIceGatheringStateChanged),
            "onSignalingStateChanged": this.logged("_onSignalingStateChanged", this._onSignalingStateChanged),
            "onError": this.logged("_onError", this._onError)
        }
    );
};

/**
 * Returns identifier of the current user
 */
MobileWebrtc.prototype.getUserId = function ()
{
    return this.userId;
};

MobileWebrtc.prototype.getUserAvatar = function(userId)
{
	if(!this.userData.hasOwnProperty(userId))
		return '';

	return this.userData[userId]['hrPhoto'] || this.userData[userId]['avatar'] || '';
};

MobileWebrtc.prototype.getUserName = function(userId)
{
	if(!this.userData.hasOwnProperty(userId))
		return '';

	return this.userData[userId]['name'] || '';
};

/**
 * Invites user
 * @param userId
 * @param video
 */
MobileWebrtc.prototype.startCall = function (userId, video)
{
    if (userId === this.getUserId() || this.callInit)
        return;

    if (this.delayedIncomingCall.chatId && this.delayedIncomingCall.senderId == userId)
    {
        this.clearDelayedCallData();
    }

    this.callInit = true;
    this.video = (video === true);
    this.initiator = true;
	this.callUserId = userId;

	webrtc.UI.show(
		webrtc.UI.state.OUTGOING_CALL,
		{
			"data": {},
			"video": this.video,
			"recipient": {
				"avatar": this.getUserAvatar(this.callUserId),
				"name": this.getUserName(this.callUserId)
			}
		}
	);

	this.sendInvite();
};

/**
 * @param repeat
 */
MobileWebrtc.prototype.sendInvite = function(repeat)
{
	repeat = (repeat === true);

	this.ajaxCall("CALL_INVITE",
		{
			'COMMAND': 'invite',
			'CHAT_ID': this.callUserId,
			'CHAT': 'N',
			'VIDEO': (this.video ? "Y" : "N")
		},
		this.logged("CALL_INVITE ANSWER", (params) =>
		{
			if (params.ERROR)
			{
				if (repeat || params.ERROR == "SESSION_ERROR")
				{
					this.resetState();
					this.showError(BX.message('MOBILEAPP_ERROR_AUTH'));
				}
				else if (params.ERROR == 'AUTHORIZE_ERROR')
				{
					Application.auth((result) =>
					{
						if(BX.type.isPlainObject(result) && result['status'] === 'success')
						{
							this.sendInvite(true);
						}
						else
						{
							this.resetState();
							this.showError(BX.message('MOBILEAPP_ERROR_AUTH'));
						}
					});
				}
				return;
			}
			this.callChatId = params.CHAT_ID;
			this.callToGroup = params.CALL_TO_GROUP;

			// BX.onCustomEvent("onMobileRTCReadyToConversation");
		}),
		(params) =>
		{
			this.resetState();
			this.showError(BX.message('MOBILEAPP_SOME_ERROR'));
		}
	);
};

/**
 * Shows incoming call screen
 * @param params
 */
MobileWebrtc.prototype.showIncomingCall = function (params)
{
    this.callChatId = params.chatId;
    this.callToGroup = false;
    this.callUserId = params.senderId;
    this.initiator = false;
    this.callInit = true;
    this.callCommand('wait');
    webrtc.UI.show(
        webrtc.UI.state.INCOMING_CALL,
        {
            "data": params,
            "video": params.video,
            "caller": {
                "name": this.getUserName(params.senderId),
                "avatar": this.getUserAvatar(params.senderId)
            }
        }
    );
};

/**
 * Clears data for delayed incoming call
 */
MobileWebrtc.prototype.clearDelayedCallData = function ()
{
    // clearTimeout(this.incomingCallTimeOutId);
    this.incomingCallTimeOutId = null;
    this.delayedIncomingCall = {};
};

/**
 * Resets all variables, connection data and states
 */
MobileWebrtc.prototype.resetState = function ()
{
    this.connected = {};
    this.initiator = false;
    this.callInit = false;
    this.video = false;
    this.callActive = false;
    this.callChatId = 0;
    this.callUserId = 0;
    this.isMobile = false;
    this.peerConnectionInited = false;
    this.iceCandidates = [];
    this.iceCandidatesToSend = [];
};

MobileWebrtc.prototype.storeRedialParameters = function ()
{
	this.redialParameters.userId = this.callUserId;
	this.redialParameters.video = this.video;
};

MobileWebrtc.prototype.resetRedialParameters = function ()
{
	this.redialParameters.userId = 0;
	this.redialParameters.video = false;
};

/**
 * Signaling
 * Sends signals to user with passed commands and params
 * @param userID
 * @param params
 */
MobileWebrtc.prototype.callSignaling = function (userID, params)
{
	let signalingParams = {
		'COMMAND': 'signaling',
		'CHAT_ID': this.callChatId,
		'RECIPIENT_ID': userID,
		'PEER': JSON.stringify(params)
	};

    this.ajaxCall("CALL_SIGNALING", signalingParams);
};

/**
 * Send command to chat with chatId
 *
 * Available commands and them meaning:
 * <pre>
 * busy - you are already have the conversation with someone and can't pick up the phone
 * busy_self - informs the partner that you already have the conversation with him
 * ready - informs the partner you have front camera and microphone, local video stream is created and you are ready for the peerdata exchange
 * wait - informs the partner to keep waiting for the answer for the 30 seconds
 * decline - informs the partner that you've declined his incoming call or hung up while the call was active
 * </pre>
 * @param command
 * @param params
 */
MobileWebrtc.prototype.callCommand = function (command, params)
{
	params = typeof(params) == 'object' ? params : {};

	let ajaxParams = {
		'COMMAND': command,
		'RECIPIENT_ID': this.callUserId,
		'PARAMS': JSON.stringify(params)
	};

	if(this.callChatId > 0)
	{
		ajaxParams.CHAT_ID = this.callChatId;
	}
	else if(this.callUserId)
	{
		ajaxParams.USER_ID = this.callUserId
	}
	else
	{
		console.log('Could not send call command');
		return;
	}

	console.log('callCommand ', command, ajaxParams);
	this.ajaxCall(
		"CALL_SHARED",
		ajaxParams
	);
};

/**
 * Finishes the conversation with closing UI
 */
MobileWebrtc.prototype.finishDialog = function ()
{
    webrtc.UI.close();
};

MobileWebrtc.prototype.showError = function(errorMessage)
{
	webrtc.UI.show(
		webrtc.UI.state.FAIL_CALL,
		{
			'message': errorMessage
		}
	);
};

MobileWebrtc.prototype.initReconnect = function ()
{
    console.log("Send reconnect");
    this.callCommand("reconnect");
    webrtc.onReconnect();
    if(this.initiator)
    {
        webrtc.createPeerConnection();
    }
};

/**
 * Handles peer data signals
 *
 * @param userId
 * @param peerData
 */
MobileWebrtc.prototype.signalingPeerData = function (userId, peerData)
{
};

/**
 * @param reqParam - request param for the ajax request
 * @param reqData - post data
 * @param onfailure - failure callback function
 * @param onsuccess - success callback function
 */
MobileWebrtc.prototype.ajaxCall = function (reqParam, reqData, onsuccess, onfailure)
{
    var data = reqData;
    data["MOBILE"] = "Y";
    data["IS_MOBILE"] = "Y";
    data["IM_CALL"] = "Y";
    data["IM_AJAX_CALL"] = "Y";
    data["sessid"] = BX.thurly_sessid();

    BX.ajax({
        url: this.signalingLink() + reqParam,
        method: 'POST',
        dataType: 'json',
        timeout: 30,
        async: true,
        data: data,
        onsuccess: onsuccess,
        onfailure: onfailure
    });
};

MobileWebrtc.prototype.appendUserData = function(users, hrphoto)
{
	if(BX.type.isPlainObject(users))
	{
		for (let userId in users)
		{
			this.userData[userId] = users[userId];
			this.userData[userId]['name'] = decodeHtml(this.userData[userId]['name']);
		}
	}

	if(BX.type.isPlainObject(hrphoto))
	{
		for (let userId in hrphoto)
		{
			this.userData[userId]['hrPhoto'] = hrphoto[userId];
		}
	}
};

MobileWebrtc.prototype.onUiDecline = function (params)
{
    this.callCommand('decline', {
        "ACTIVE": (this.callActive) ? "Y" : "N",
        "INITIATOR": (this.initiator) ? "Y" : "N"
    });
    this.resetState();
};

MobileWebrtc.prototype.onUiAnswer = function (params)
{
    webrtc.UI.show(
        webrtc.UI.state.CONVERSATION
    );
    webrtc.getUserMedia({video: params.video});
    this.waitTimeout = false;
    this.callToGroup = false;
    this.callChatId = params.chatId;
    this.callUserId = params.senderId;
    this.callActive = true;
    this.initiator = false;
    this.ajaxCall(
        "CALL_ANSWER",
        {
            'COMMAND': 'answer',
            'CHAT_ID': this.callChatId,
            'CALL_TO_GROUP': this.callToGroup ? 'Y' : 'N',
            'RECIPIENT_ID': this.callUserId
        }
    );
};

MobileWebrtc.prototype.onUiCallback = function ()
{
    if (this.redialParameters.userId > 0)
	{
		this.startCall(this.redialParameters.userId, this.redialParameters.video);
		this.resetRedialParameters();
	}

};

MobileWebrtc.prototype.onUiClose = function ()
{
    this.resetState();
    this.resetRedialParameters();
};

MobileWebrtc.prototype._onDisconnect = function ()
{
    this.peerConnectionInited = false;
    //send reconnect
};

MobileWebrtc.prototype._onIceCandidateDiscovered = function (params)
{
    this.iceCandidatesToSend.push({
        type: 'candidate',
        label: params.candidate.sdpMLineIndex,
        id: params.candidate.sdpMid,
        candidate: params.candidate.candidate
    });

    clearTimeout(this.iceCandidateTimeout);
    this.iceCandidateTimeout = setTimeout(BX.delegate(function ()
    {
        if (this.iceCandidatesToSend.length === 0)
            return false;

        this._onIceCandidate(this.callUserId, {'type': 'candidate', 'candidates': this.iceCandidatesToSend});
        this.iceCandidatesToSend = [];
    }, this), 250);
};

MobileWebrtc.prototype._onPeerConnectionCreated = function ()
{
	clearTimeout(this.checkConnectionTimeout);
    this.peerConnectionInited = true;
    if (this.initiator)
    {
        webrtc.createOffer();
    }
    else {
        webrtc.createAnswer({
            "sdp": this.remoteSessionDescription
        });
    }
};

MobileWebrtc.prototype._onIceCandidate = function (userID, candidates)
{
    this.callSignaling(userID, candidates);
};

MobileWebrtc.prototype._onIceConnectionStateChanged = function (state)
{
    this.iceCandidateState = state.toLowerCase();
    clearTimeout(this.checkConnectionTimeout);
    this.checkConnectionTimeout = setTimeout(BX.delegate(this.reconnectIfNeeded, this), 15000);
};

MobileWebrtc.prototype._onIceGatheringStateChanged = function (params)
{
    //TODO to do something
};

MobileWebrtc.prototype._onSignalingStateChanged = function (params)
{
    //TODO to do something
};

MobileWebrtc.prototype._onLocalSessionDescriptionCreated = function (params)
{
    this.sessionDescription = params;
    if (this.iceCandidates.length > 0)
    {
        webrtc.addIceCandidates(this.iceCandidates);
        this.iceCandidates = [];
    }

    this.callSignaling(this.callUserId, this.sessionDescription);
};

MobileWebrtc.prototype._onUserMediaSuccess = function (params)
{
    webrtc.UI.showLocalVideo();
    this.connected[this.getUserId()] = true;
    this.callCommand("ready");
    if (this.connected[this.callUserId] && this.initiator)
    {
        webrtc.createPeerConnection();
    }
};

MobileWebrtc.prototype._onError = function (errorData)
{
	//TODO handle error
	this.resetState();
};

MobileWebrtc.prototype.reconnectIfNeeded = function ()
{
    if(!this.peerConnectionInited)
        return false;
    if(this.iceCandidateState === "completed" || this.iceCandidateState === "stable" || this.iceCandidateState === "connected")
        return false;

    this.initReconnect();
};

/**
 *
 * @param {function} onSuccess
 */
MobileWebrtc.prototype.execIfNotTooLate = function(onSuccess)
{
	return (params, extra) =>
	{
		const isTooLate = extra.server_time_ago >= eventTimeRange;

		if(isTooLate)
		{
			console.log("Call was started too long time ago");
			return false;
		}

		return onSuccess.call(this, params, extra);
	}
};

MobileWebrtc.prototype.onPullEvent = function(command, params, extra)
{
	if(command !== 'call')
		return;

	const handlers = {
		ready: this.logged("onPullCommandReady", this.onPullCommandReady),
		decline: this.logged("onPullCommandDecline", this.onPullCommandDecline),
		end_call: this.logged("onPullCommandDecline", this.onPullCommandDecline), 	//this.onPullCommandEndCall ?
		waitTimeout: this.logged("onPullCommandWaitTimeout", this.onPullCommandWaitTimeout),
		invite: this.logged("onPullCommandInvite", this.execIfNotTooLate(this.onPullCommandInvite)),
		invite_join: this.logged("onPullCommandInvite", this.onPullCommandInvite),
		answer: this.logged("onPullCommandAnswer", this.onPullCommandAnswer),
		decline_self: this.logged("onPullCommandDeclineSelf", this.onPullCommandDeclineSelf),
		answer_self: this.logged("onPullCommandAnswerSelf", this.onPullCommandAnswerSelf),
		signaling: this.logged("onPullCommandSignaling", this.onPullCommandSignaling),
		busy: this.logged("onPullCommandBusy", this.onPullCommandBusy),
		errorAccess: this.logged("onPullCommandErrorAccess", this.onPullCommandErrorAccess),
		reconnect: this.logged("onPullCommandReconnect", this.onPullCommandReconnect)
	};

	if (handlers.hasOwnProperty(params.command))
	{
		handlers[params.command](params, extra);
	}
};

MobileWebrtc.prototype.onPullCommandReady = function(params)
{
	this.connected[params.senderId] = true;

	if(this.connected[this.getUserId()] && this.initiator === true)
	{
		webrtc.createPeerConnection();
	}
};

MobileWebrtc.prototype.onPullCommandDecline = function(params)
{
	if (this.callInit)
	{
		if (this.callChatId == params.chatId)
		{
			if (this.initiator && !this.connected[this.callUserId])
			{
				this.storeRedialParameters();
				webrtc.UI.show(
					webrtc.UI.state.FAIL_CALL,
					{
						'message': BX.message("MOBILEAPP_CALL_DECLINE")
					}
				);
			}
			else {
				webrtc.UI.close();
			}

			this.resetState()
		}
	}
	else if (this.delayedIncomingCall.chatId == params.chatId)
	{
		this.clearDelayedCallData();
	}
};

MobileWebrtc.prototype.onPullCommandEndCall = function(params)
{
	if ((this.delayedIncomingCall.chatId && this.delayedIncomingCall.chatId == params.chatId))
	{
		if (this.delayedIncomingCall.chatId == params.chatId)
		{
			// clearTimeout(this.incomingCallTimeOutId);
			this.incomingCallTimeOutId = null;
			this.delayedIncomingCall = {};
		}
	}
};

MobileWebrtc.prototype.onPullCommandWaitTimeout = function(params)
{
	if (this.callChatId == params.chatId)
	{
		this.resetState();
		this.finishDialog();
	}
};

MobileWebrtc.prototype.onPullCommandInvite = function(params, extra)
{
	if(params.callToGroup)
	{
		console.log("Call to group");
		return;
	}

	this.appendUserData(params.users, params.hrphoto);

	if (!this.callInit)
	{
		if (this.incomingCallTimeOutId === null)
		{
			this.delayedIncomingCall = params;
			this.incomingCallTimeOutId = setTimeout(BX.delegate(function ()
			{
				this.showIncomingCall(this.delayedIncomingCall);
				this.delayedIncomingCall = {};
				this.incomingCallTimeOutId = null;
			}, this), this.incomingCallTimeOut * 1000);
		}
	}
	else if (params.command == 'invite')
	{
		if (this.callChatId == params.chatId)
		{
			this.callCommand('busy_self');
		}
		else
		{
			this.ajaxCall("CALL_BUSY", {
				'COMMAND': 'busy',
				'CHAT_ID': params.chatId,
				'RECIPIENT_ID': params.senderId,
				'VIDEO': params.video ? 'Y' : 'N'
			});
		}
	}
	else if (this.initiator && this.callChatId == params.chatId && !this.callActive)
	{
		this.onUiAnswer(params);
	}
};

MobileWebrtc.prototype.onPullCommandAnswer = function(params)
{
	if(!this.initiator)
		return;
		
	if (this.callInit)
	{
		this.initiator = true;
		this.callActive = true;
		webrtc.UI.show(webrtc.UI.state.CONVERSATION);
		if (typeof params.video == "undefined")
		{
			params.video = this.video;
		}
		webrtc.getUserMedia({video: params.video});
	}
};

MobileWebrtc.prototype.onPullCommandDeclineSelf = function(params)
{
	if(this.callChatId != params.chatId)
		return;

	if (this.delayedIncomingCall.chatId == params.chatId)
	{
		this.clearDelayedCallData();
	}

	this.resetState();
	this.finishDialog();
};

MobileWebrtc.prototype.onPullCommandAnswerSelf = function(params)
{
	if(this.callActive)
		return;

	if (this.delayedIncomingCall.chatId == params.chatId)
	{
		this.clearDelayedCallData();
	}

	this.resetState();
	this.finishDialog();
};

MobileWebrtc.prototype.onPullCommandSignaling = function(params)
{
	if(!this.callInit)
		return;

	if(!this.callChatId == params.chatId)
		return;

	if(!this.connected[this.getUserId()])
		return;

	let signal = JSON.parse(params.peer);

	if (signal.type === 'offer')
	{
		this.remoteSessionDescription = signal["sdp"];
		webrtc.createPeerConnection();
	}
	else if (signal.type === 'answer')
	{
		webrtc.setRemoteDescription(signal);
	}
	else if (signal.type === 'candidate')
	{
		if (this.peerConnectionInited)
		{
			webrtc.addIceCandidates(signal.candidates);
		}
		else
		{
			for (let i = 0; i < signal.candidates.length; i++)
			{
				this.iceCandidates.push(signal.candidates[i]);
			}
		}
	}
};

MobileWebrtc.prototype.onPullCommandBusy = function(params)
{
	if(!this.callInit)
		return;

	if(this.callChatId != params.chatId)
		return;

	this.storeRedialParameters();
	webrtc.UI.show(
		webrtc.UI.state.FAIL_CALL,
		{
			'message': BX.message("MOBILEAPP_CALL_BUSY")
		}
	);
	this.resetState();
};

MobileWebrtc.prototype.onPullCommandErrorAccess = function(params)
{
	if(!this.callInit)
		return;

	if(this.callChatId != params.chatId)
		return;

	this.storeRedialParameters();
	webrtc.UI.show(webrtc.UI.state.FAIL_CALL,
	{
		'message': BX.message("MOBILEAPP_CALL_NO_ACCESS")
	});
	this.callInit = false;
};

MobileWebrtc.prototype.onPullCommandReconnect = function(params)
{
	if(!this.callInit)
		return;

	if(this.callChatId != params.chatId)
		return;

	this.initiator = false;
	webrtc.onReconnect();
};

MobileWebrtc.prototype.logged = function(name, cb)
{
	let self = this;
	return function()
	{
		let params = [name].concat(arguments);
		console.log.apply(null, params);
		cb.apply(self, arguments);
	}
};

// endregion Mobile webrtc

// region Mobile telephony

MobileTelephony = function()
{
	this.ui = null;
	this.mobileVoximplant = null;

	//from checkout
	this.userId = BX.componentParameters.get('userId', 0);
	this.isAdmin = BX.componentParameters.get('isAdmin', false);
	this.server = BX.componentParameters.get('voximplantServer', '');
	this.login = BX.componentParameters.get('voximplantLogin', '');
	this.voximplantInstalled = BX.componentParameters.get('voximplantInstalled', false);
	this.canPerformCalls = BX.componentParameters.get('canPerformCalls', false);
	this.lines = BX.componentParameters.get('lines',  {});
	this.defaultLineId = BX.componentParameters.get('defaultLineId', '');

	this.call = null;
	this.callId = '';
	this.phoneNumber = null;
	this.lineNumber = '';
	this.phoneRinging = 0;
	this.callConfig = {};
	this.callDevice = 'PHONE';
	this.crmData = {};
	this.transferUser = 0;

	// flags
	this.callInit = false;
	this.callActive = false;
	this.debug = false;
	this.connected = false;
	this.authorized = false;
	this.ignoreAnswerSelf = false;
	this.isIncoming = false;
	this.isTransfer = false;
	this.isRestCall = false;
	this.formShown = false;
	this.portalCall = false;

	this.promises = {
		authorization: null,
		connection: null
	};

	this.init();
};

MobileTelephony.prototype.init = function()
{
	this.ui = new VoiceCallForm({
		eventListener: this.onUiEvent.bind(this)
	});

	this.mobileVoximplant = new Voximplant({
		listeners: {
			onIncomingCall: this._onIncomingCall.bind(this),
			onConnectionEstablished: this._onConnectionEstablished.bind(this),
			onConnectionClosed: this._onConnectionClosed.bind(this),
			onConnectionFailed: this._onConnectionFailed.bind(this),
			onAuthResult: this._onAuthResult.bind(this),
			onOneTimeKeyGenerated: this._onOneTimeKeyGenerated.bind(this)
		}
	});
	this.mobileVoximplant.disconnect();
	BX.addCustomEvent("onPhoneTo", this.onPhoneTo.bind(this));
	BX.addCustomEvent("onNumpadRequestShow", this.onNumpadRequestShow.bind(this));
	BX.addCustomEvent("onPullEvent-voximplant", this.onPullEvent.bind(this));
	BX.addCustomEvent("onAppActive", this.onAppActive.bind(this));
	this.onAppActive();
};

MobileTelephony.prototype.onAppActive = function()
{
	let push = preparePush(Application.getLastNotification());
	if (BX.type.isNotEmptyString(push['ACTION']) && push['ACTION'].indexOf('VI_CALL_') === 0)
	{
		this.log("Starting application from push", push);
		let params = push.PARAMS || {};
		if(params.callId)
		{
			this._onCallInvite(params);
		}
	}
};

MobileTelephony.prototype.onPhoneTo = function(e)
{
	this.log("onPhoneTo", e);
	let number = e.number;
	let params = e.params;

	params = params || {};
	if (typeof(params) != 'object')
	{
		try { params = JSON.parse(params); } catch(e) { params = {}; }
	}

	if (this.canUseTelephony())
	{
		this.phoneCall(number, params);
	}
};

MobileTelephony.prototype.onNumpadRequestShow = function()
{
	this.log("onNumpadRequestShow");

	if(Application.getApiVersion() >= 22)
	{
		if (this.canUseTelephony())
		{
			this.ui.showNumpad();
		}
	}
};

MobileTelephony.prototype.canUseTelephony = function()
{
	return this.voximplantInstalled && this.canPerformCalls;
};

MobileTelephony.prototype.onUiEvent = function(params)
{
	this.log("onUiEvent: ", params);
	let eventName = params.eventName;

	let handlers = {
		[TelephonyUiEvent.onHangup]: this._onUiHangup,
		[TelephonyUiEvent.onSpeakerphoneChanged]: this._onUiSpeakerphoneChanged,
		[TelephonyUiEvent.onMuteChanged]: this._onUiMuteChanged,
		[TelephonyUiEvent.onPauseChanged]: this._onUiPauseChanged,
		[TelephonyUiEvent.onCloseClicked]: this._onUiCloseClicked,
		[TelephonyUiEvent.onSkipClicked]: this._onUiSkipClicked,
		[TelephonyUiEvent.onAnswerClicked]: this._onUiAnswerClicked,
		[TelephonyUiEvent.onNumpadButtonClicked]: this._onUiNumpadButtonClicked,
		[TelephonyUiEvent.onPhoneNumberReceived]: this._onUiPhoneNumberReceived,
		//[TelephonyUiEvent.onContactListChoose]: this._onUiContactListChoose,
		//[TelephonyUiEvent.onContactListMenuChoose]: this._onUiContactListMenuChoose
	};

	if(BX.type.isFunction(handlers[eventName]))
	{
		handlers[eventName].call(this, params);
	}
	else if(eventName.substr(0, 4) == "crm_")
	{
		let crmParams = eventName.split("_");
		this._onUiCrmLinkClick({
			'entityType': crmParams[1],
			'entityId': crmParams[2]
		});
	}
};

MobileTelephony.prototype.onPullEvent = function(command, params, extra)
{
	this.log("onPullEvent-voximplant", command, params, extra);

	let handlers = {
		'invite': this._onPullEventInvite.bind(this),
		'answer_self': this._onPullEventAnswerSelf.bind(this),
		'timeout': this._onPullEventTimeout.bind(this),
		'outgoing': this._onPullEventOutgoing.bind(this),
		'start': this._onPullEventStart.bind(this),
		'hold': this._onPullEventHold.bind(this),
		'unhold': this._onPullEventUnHold.bind(this),
		'update_crm': this._onPullEventUpdateCrm.bind(this),
		//'inviteTransfer': this._onPullEventInviteTransfer,
		//'cancelTransfer': this._onPullEventCancelTransfer,
		//'timeoutTransfer': this._onPullEventCancelTransfer,
		//'declineTransfer': this._onPullEventDeclineTransfer,
		//'completeTransfer': this._onPullEventCompleteTransfer,
		//'phoneDeviceActive': this._onPullEventPhoneDeviceActive,
		'changeDefaultLineId': this._onPullEventChangeDefaultLineId.bind(this),
		'replaceCallerId': this._onPullEventReplaceCallerId.bind(this),
		//'showExternalCall': this._onPullEventShowExternalCall,
		//'hideExternalCall': this._onPullEventHideExternalCall
	};

	if (BX.type.isFunction(handlers[command]))
	{
		handlers[command](params, extra);
	}
};

MobileTelephony.prototype.answerCall = function()
{
	this.setUiState(TelephonyUiState.WAITING);
	this.setUiStateLabel(BX.message('IM_M_CALL_ST_CONNECT'));
	BX.rest.callMethod(
		'voximplant.call.answer', {'CALL_ID' : this.callId}
	).catch(
		(data) =>
		{
			let answer = data.answer;
			this.log('voximplant.call.answer error: ', answer);

			// call could be already finished by this moment
			if(!this.callInit && !this.callActive)
				return;

			this.setUiState(TelephonyUiState.FINISHED);
			if(answer.error === 'ERROR_NOT_FOUND')
				this.setUiStateLabel(BX.message("IM_M_CALL_ALREADY_FINISHED"));
			else if(answer.error === 'ERROR_WRONG_STATE')
				this.setUiStateLabel(BX.message("IM_M_CALL_ALREADY_ANSWERED"));
			else
				this.setUiStateLabel(BX.message("IM_PHONE_ERROR"));
		}
	).then(
		() => this.connect()
	).then(
		() => this.authorize()
	).then(
		() => BX.rest.callMethod('voximplant.call.sendReady', {'CALL_ID' : this.callId})
	);
};

MobileTelephony.prototype.answerTransferCall = function()
{
	this.setUiState(TelephonyUiState.WAITING);
	this.setUiStateLabel(BX.message('IM_M_CALL_ST_CONNECT'));
	BX.rest.callMethod('voximplant.call.answerTransfer', {'CALL_ID' : this.callId});

	this.connect().then(
		() => this.authorize()
	).then(
		() => BX.rest.callMethod('voximplant.call.readyTransfer', {'CALL_ID' : this.callId})
	);
};

MobileTelephony.prototype.phoneCall = function(number, params)
{
	if (!this.canUseTelephony())
		return false;

	this.log("phoneCall", number, params);

	let correctNumber = this.phoneCorrect(number);

	if (!BX.type.isPlainObject(params))
		params = {};

	if (correctNumber.length <= 0)
	{
		//this.BXIM.openConfirm({title: BX.message('IM_PHONE_WRONG_NUMBER'), message: BX.message('IM_PHONE_WRONG_NUMBER_DESC')});
		this.log("Wrong number");
		return false;
	}

	if (this.callActive || this.callInit)
		return false;

	this.ui.playSound({soundId: Sound.startCall});

	if(this.isRestLine(this.defaultLineId))
	{
		this.startCallViaRest(number, this.defaultLineId, params);
		return;
	}

	this.callInit = true;
	this.callActive = false;
	this.phoneNumber = correctNumber;
	this.phoneParams = params;

	this.showCallForm({
		status : BX.message('IM_M_CALL_ST_CONNECT'),
		state : TelephonyUiState.OUTGOING
	});

	this.connect().then(this.authorize.bind(this)).then(this.startCall.bind(this));
};

MobileTelephony.prototype.startCallViaRest = function(number, lineId, params)
{
	let appName = this.getRestAppName(lineId);
	this.isRestCall = true;
	this.phoneNumber = number;
	this.showCallForm({
		status : BX.message('IM_PHONE_OUTGOING_REST').replace('#APP_NAME#', appName),
		state : TelephonyUiState.FINISHED
	});
	BX.rest.callMethod('voximplant.call.startViaRest',
	{
		'NUMBER': number,
		'LINE_ID': lineId,
		'PARAMS': params
	}).then((result) =>
	{
		this.log('voximplant.call.startViaRest: ', result);
		let data = result.data();
		if(data.DATA && data.DATA.CRM)
			this.setCrmData(data.DATA.CRM);
	});
};

MobileTelephony.prototype.startCall = function()
{
	this.call = new TelephonyCall(this.phoneNumber, false, this.phoneParams, this.mobileVoximplant);

	this.call.addEventListener(CallEvent.Connected, this._onCallConnected.bind(this));
	this.call.addEventListener(CallEvent.Disconnected, this._onCallDisconnected.bind(this));
	this.call.addEventListener(CallEvent.Failed, this._onCallFailed.bind(this));
	this.call.addEventListener(CallEvent.ProgressToneStart, this._onCallProgressToneStart.bind(this));
	this.call.addEventListener(CallEvent.ProgressToneStop, this._onCallProgressToneStop.bind(this));

	this.call.start();
};

MobileTelephony.prototype.finishCall = function()
{
	if(this.call)
	{
		this.call.hangup();
		this.call = null;
	}

	if(this.promises.connection)
	{
		this.promises.connection.reject();
		this.promises.connection = null;
	}

	if(this.promises.authorization)
	{
		this.promises.authorization.reject();
		this.promises.authorization = null;
	}

	this.callId = '';
	this.phoneNumber = null;
	this.lineNumber = '';
	this.phoneRinging = 0;
	this.callConfig = {};
	this.callDevice = 'PHONE';
	this.crmData = {};
	this.transferUser = 0;

	// flags
	this.callInit = false;
	this.callActive = false;
	this.ignoreAnswerSelf = false;
	this.isIncoming = false;
	this.isTransfer = false;
	this.isRestCall = false;
	this.portalCall = false;

	this.ui.pauseTimer();
};

MobileTelephony.prototype.sendSkip = function()
{
	if (this.callInit && this.isTransfer && !this.phoneTransferUser)
	{
		BX.rest.callMethod('voximplant.call.declineTransfer', {'CALL_ID': this.callId});
	}
	else if (this.callInit && this.isIncoming)
	{
		BX.rest.callMethod('voximplant.call.skip', {'CALL_ID': this.callId});
	}
};

MobileTelephony.prototype.authorize = function()
{
	this.log('authorize');
	let result = new BX.Promise();
	if(this.connected && this.authorized)
	{
		result.fulfill();
	}
	else
	{
		this.promises.authorization = result;
		if(this.login && this.server)
		{
			this.mobileVoximplant.requestOneTimeKeyWithUsername(this.login + '@' + this.server);
		}
		else
		{
			BX.rest.callMethod('voximplant.authorization.get').then(result =>
			{
				let data = result.data();
				this.log('authorization data: ', data);
				this.login = data.LOGIN;
				this.server = data.SERVER;
				this.mobileVoximplant.requestOneTimeKeyWithUsername(this.login + '@' + this.server);
			}).catch((data) =>
			{
				let error = data.error();
				let answer = data.answer;

				this.log('voximplant.authorization.get error', error);
				if(answer.error_description)
					this.setUiStateLabel(answer.error_description);
				else if(error.status == '401')
					this.setUiStateLabel(BX.message('IM_PHONE_401'));
				else
					this.setUiStateLabel(BX.message('IM_PHONE_403'));

				this.setUiState(TelephonyUiState.FINISHED);
			});
		}
	}

	return result;
};

MobileTelephony.prototype.loginWithOneTimeKey = function(oneTimeKey)
{
	this.log('loginWithOneTimeKey');
	BX.rest.callMethod('voximplant.authorization.signOneTimeKey', {KEY: oneTimeKey}).then(result =>
	{
		let data = result.data();
		let hash = data.HASH;

		this.mobileVoximplant.setAuthData({
			login: this.login + '@' + this.server,
			hash: hash
		});

		this.mobileVoximplant.loginWithOneTimeKey();
	}).catch(e =>
	{
		this.log('voximplant.authorization.signOneTimeKey error', e.error());
		this.setUiStateLabel(BX.message('IM_PHONE_403'));
		this.setUiState(TelephonyUiState.FINISHED);
	})
};

MobileTelephony.prototype.connect = function()
{
	this.log('connect');
	let result = new BX.Promise();

	if(this.connected)
	{
		result.fulfill();
	}
	else
	{
		this.mobileVoximplant.connect();
		this.promises.connection = result;
	}

	return result;
};

MobileTelephony.prototype.disconnect = function()
{
	this.log('disconnect');
	this.mobileVoximplant.disconnect();
};

MobileTelephony.prototype.setCallHold = function(holdState)
{
	if (!this.call)
		return false;

	this.call.sendMessage({'COMMAND': (holdState ? 'hold' : 'unhold')});
};

MobileTelephony.prototype.getUiFields = function()
{
	let headerLabels = {};
	let middleLabels = {};
	let middleButtons = {};
	let avatarUrl = '';

	if (this.crmData.FOUND == 'Y')
	{
		let crmContactName = this.crmData.CONTACT && this.crmData.CONTACT.NAME? this.crmData.CONTACT.NAME: '';
		let crmContactPhoto = this.crmData.CONTACT && this.crmData.CONTACT.PHOTO? this.crmData.CONTACT.PHOTO: '';
		let crmContactPost = this.crmData.CONTACT && this.crmData.CONTACT.POST? this.crmData.CONTACT.POST: '';
		let crmCompanyName = this.crmData.COMPANY? this.crmData.COMPANY: '';

		if (!this.portalCall && !this.isRestCall && this.callConfig.hasOwnProperty('RECORDING'))
		{
			if (this.callConfig.RECORDING == "Y")
			{
				headerLabels.thirdSmallHeader = {'text': BX.message('IM_PHONE_REC_ON'), textColor: "#ecd748"};
			}
			else
			{
				headerLabels.thirdSmallHeader = {'text': BX.message('IM_PHONE_REC_OFF'), textColor: "#ee423f"};
			}
		}

		if (crmContactName)
			headerLabels.firstHeader = {'text': crmContactName};
		if (crmContactPost)
			headerLabels.firstSmallHeader = {'text': crmContactPost};
		if (crmCompanyName)
			headerLabels.secondSmallHeader = {'text': crmCompanyName};

		avatarUrl = crmContactPhoto ?  currentDomain + encodeURI(crmContactPhoto) : '';

		if (this.crmData.DEALS && this.crmData.DEALS.length > 0)
		{
			middleLabels = {
				infoTitle: {
					text: ""
				},
				infoDesc: {
					text: this.crmData.DEALS[0].TITLE
				},
				infoHeader: {
					text: this.crmData.DEALS[0].STAGE,
					textColor: this.crmData.DEALS[0].STAGE_COLOR
				},
				infoSum: {
					text: decodeHtml(this.crmData.DEALS[0].OPPORTUNITY)
				}
			};

			if (this.crmData.DEAL_URL)
			{
				middleButtons['button1'] = {
					text: BX.message('IM_PHONE_ACTION_T_DEAL'),
					sort: 100,
					eventName: "crm_deal_"+this.crmData.DEALS[0].ID
				};
			}
		}

		let dataSelect = [];
		if (this.crmData.COMPANY_DATA && this.crmData.CONTACT_DATA)
		{
			dataSelect = ['CONTACT_DATA', 'COMPANY_DATA', 'LEAD_DATA'];
		}
		else if (this.crmData.CONTACT_DATA && this.crmData.LEAD_DATA)
		{
			dataSelect = ['CONTACT_DATA', 'LEAD_DATA'];
		}
		else if (this.crmData.LEAD_DATA && this.crmData.COMPANY_DATA)
		{
			dataSelect = ['LEAD_DATA', 'COMPANY_DATA'];
		}
		else
		{
			if (this.crmData.CONTACT_DATA)
			{
				dataSelect = ['CONTACT_DATA'];
			}
			else if (this.crmData.COMPANY_DATA)
			{
				dataSelect = ['COMPANY_DATA'];
			}
			else if (this.crmData.LEAD_DATA)
			{
				dataSelect = ['LEAD_DATA'];
			}
		}

		for (let i = 0; i < dataSelect.length; i++)
		{
			let type = dataSelect[i];
			if (this.crmData[type])
			{
				if (type == 'CONTACT_DATA')
				{
					middleButtons['buttonData'+i] = {
						text: BX.message('IM_PHONE_ACTION_T_CONTACT'),
						sort: 200+i,
						eventName: "crm_contact_"+this.crmData[type].ID
					};
				}
				else if (type == 'COMPANY_DATA')
				{
					middleButtons['buttonData'+i] = {
						text: BX.message('IM_PHONE_ACTION_T_COMPANY'),
						sort: 200+i,
						eventName: "crm_company_"+this.crmData[type].ID
					};
				}
				else if (type == 'LEAD_DATA')
				{
					middleButtons['buttonData'+i] = {
						text: BX.message('IM_PHONE_ACTION_T_LEAD'),
						sort: 200+i,
						eventName: "crm_lead_"+this.crmData[type].ID
					};
				}
			}
		}
		if(this.portalCall)
		{
			middleLabels.imageStub = {backgroundColor: '#464f58', display: 'visible'};
		}
	}
	else
	{
		let phoneNumber;
		if (!this.phoneNumber || this.phoneNumber == 'hidden')
		{
			phoneNumber = BX.message('IM_PHONE_HIDDEN_NUMBER');
		}
		else
		{
			phoneNumber = this.phoneNumber.toString();

			if (phoneNumber.substr(0, 1) !== '8' && phoneNumber.substr(0, 1) !== '+' && !isNaN(parseInt(phoneNumber)) && phoneNumber.length >= 10)
			{
				phoneNumber = '+' + phoneNumber;
			}
		}
		headerLabels.firstHeader = {'text': phoneNumber};

		headerLabels.firstSmallHeader = {};
		if(this.isIncoming)
		{
			headerLabels.firstSmallHeader.text = this.lineNumber ?  BX.message('IM_PHONE_CALL_TO_PHONE').replace('#PHONE#', this.lineNumber): BX.message('IM_VI_CALL');
		}
		else
		{
			headerLabels.firstSmallHeader.text = BX.message('IM_PHONE_OUTGOING');
		}
		headerLabels.firstSmallHeader.textColor = "#999999";

		if (!this.portalCall && !this.isRestCall && this.callConfig.hasOwnProperty('RECORDING'))
		{
			if (this.callConfig.RECORDING == "Y")
			{
				headerLabels.thirdSmallHeader = {text: BX.message('IM_PHONE_REC_ON'), textColor: "#ecd748"};
			}
			else
			{
				headerLabels.thirdSmallHeader = {text: BX.message('IM_PHONE_REC_OFF'), textColor: "#ee423f"};
			}
		}

		middleLabels.imageStub = {backgroundColor: '#464f58', display: 'visible'};

		/*let middleButtons = {
			button1: {
				text: BX.message('IM_CRM_BTN_NEW_LEAD'),
				sort:100,
				eventName: "button1"
			},
			button2: {
				text: BX.message('IM_CRM_BTN_NEW_CONTACT'),
				sort:1,
				eventName: "button2"
			}
		};
		this.ui.updateMiddle({}, middleButtons);*/
	}

	return {headerLabels, middleLabels, middleButtons, avatarUrl};
};


MobileTelephony.prototype.showCallForm = function(params)
{
	let callFormParams = this.getUiFields();

	if (params.status)
	{
		callFormParams.footerLabels =
		{
			callStateLabel:
			{
				text: params.status
			}
		};
	}
	if (params.state)
	{
		callFormParams.state = params.state;
	}

	this.log('callFormParams: ', callFormParams);
	this.ui.show(callFormParams);
	this.formShown = true;
};

MobileTelephony.prototype.updateCallForm = function()
{
	if(!this.formShown)
		return false;

	let {headerLabels, middleLabels, middleButtons, avatarUrl} = this.getUiFields();

	this.ui.updateHeader({headerLabels, avatarUrl});
	this.ui.updateMiddle({middleLabels, middleButtons});
};

MobileTelephony.prototype.closeCallForm = function()
{
	this.ui.closeNumpad();
	this.ui.close();
	this.formShown = false;
};

MobileTelephony.prototype.setCrmData = function(crmData)
{
	if(!BX.type.isPlainObject(crmData))
		crmData = {FOUND: 'N'};

	this.crmData = crmData;
	if(this.crmData.FOUND === 'Y')
	{
		this.updateCallForm();
	}
};

MobileTelephony.prototype.setUiStateLabel = function(stateLabel)
{
	if(this.formShown)
	{
		this.ui.updateFooter({footerLabels: {callStateLabel: {text: stateLabel}}});
	}
};

MobileTelephony.prototype.setUiState = function(uiState)
{
	if(this.formShown)
	{
		this.ui.updateFooter({state: uiState})
	}
};

MobileTelephony.prototype.setProgress = function(progress)
{
	if (progress == 'connect')
	{
	}
	else if (progress == 'wait')
	{
		this.setUiState(TelephonyUiState.WAITING);
	}
	else if (progress == 'online')
	{
		if (!this.portalCall)
		{
			let headerLabels = {};
			if (this.callConfig.RECORDING == "Y")
			{
				headerLabels.thirdSmallHeader = {'text' : BX.message('IM_PHONE_REC_NOW'), textColor : "#7fc62c"};
			}
			else
			{
				headerLabels.thirdSmallHeader = {'text': BX.message('IM_PHONE_REC_OFF'), textColor: "#ee423f"};
			}

			this.ui.updateHeader({headerLabels});
		}

		this.setUiState(TelephonyUiState.STARTED);
	}
	else if (progress == 'offline' || progress == 'error')
	{
		if (progress == 'offline')
		{
			if (!this.portalCall)
			{
				let headerLabels = {};
				if (this.callConfig.RECORDING == "Y" && this.phoneCallTime > 0)
				{
					headerLabels.thirdSmallHeader = {'text' : BX.message('IM_PHONE_REC_DONE'), textColor : "#7fc62c"};
				}
				else
				{
					headerLabels.thirdSmallHeader = {'text' : ''};
				}
				this.ui.updateHeader({headerLabels});

				let footerLabels = {};
				if (this.crmData.LEAD_DATA && !this.crmData.CONTACT_DATA && !this.crmData.COMPANY_DATA && this.callConfig.CRM_CREATE == 'lead')
				{
					footerLabels.actionDoneHint = {'text': BX.message('IM_PHONE_LEAD_SAVED')};
				}
				else
				{
					footerLabels.actionDoneHint = {'text': ''};
				}
				this.ui.updateFooter({footerLabels});
			}
		}
		else
		{
			let headerLabels = {};
			headerLabels.thirdSmallHeader = {'text': ''};
			this.ui.updateHeader({headerLabels});

			let footerLabels = {};
			footerLabels.actionDoneHint = {'text': ''};
			this.ui.updateFooter({footerLabels});
		}

		this.setUiState(TelephonyUiState.FINISHED);
		if(this.formShown)
		{
			this.ui.expand();
		}
	}
};

MobileTelephony.prototype.isRestLine = function(lineId)
{
	return (this.lines.hasOwnProperty(lineId) && this.lines[lineId]['TYPE'] === 'REST');
};

MobileTelephony.prototype.getRestAppName = function(lineId)
{
	if(!this.lines.hasOwnProperty(lineId))
		return '';

	if(this.lines[lineId]['TYPE'] !== 'REST')
		return '';

	let lineName = this.lines[lineId]['FULL_NAME'];
	return lineName.substr(lineName.indexOf(':') + 2);
};

MobileTelephony.prototype._onIncomingCall = function(e)
{
	this.log("_onIncomingCall", e);
	if(this.call)
	{
		this.log('call already exists');
		return;
	}

	this.call = new TelephonyCall(e.displayName, e.videoCall, e.headers, this.mobileVoximplant, e.callId);
	this.call.addEventListener(CallEvent.Connected, this._onCallConnected.bind(this));
	this.call.addEventListener(CallEvent.Disconnected, this._onCallDisconnected.bind(this));
	this.call.addEventListener(CallEvent.Failed, this._onCallFailed.bind(this));
	this.call.answer();
};

MobileTelephony.prototype._onConnectionEstablished = function(e)
{
	this.log("_onConnectionEstablished", e);
	this.connected = true;
	if(this.promises.connection)
	{
		this.promises.connection.fulfill();
		this.promises.connection = null;
	}
};

MobileTelephony.prototype._onConnectionClosed = function(e)
{
	this.log("_onConnectionClosed", e);
	if(this.promises.connection)
	{
		this.promises.connection.reject();
		this.promises.connection = null;
	}
	this.connected = false;
	this.authorized = false;
	if(this.callInit || this.callActive)
	{
		this.setProgress('error');
		this.setUiStateLabel(BX.message('IM_M_CALL_ERR'));
		this.finishCall();
	}
};

MobileTelephony.prototype._onConnectionFailed = function(e)
{
	this.log("_onConnectionFailed", e);
	this.connected = false;
	this.authorized = false;

	if(this.callInit || this.callActive)
    {
		this.setProgress('error');
		this.setUiStateLabel(BX.message('IM_M_CALL_ERR'));
		this.finishCall();
	}
};

MobileTelephony.prototype._onOneTimeKeyGenerated = function(e)
{
	this.log("_onOneTimeKeyGenerated", e);
	let oneTimeKey = e.key;
	this.loginWithOneTimeKey(oneTimeKey);
};

MobileTelephony.prototype._onAuthResult = function(e)
{
	if(e.hasOwnProperty('key'))
	{
		// android sdk fires event onAuthResult instead of onOneTimeKeyGenerated
		return this._onOneTimeKeyGenerated(e);
	}
	if(e.result)
	{
		if(this.promises.authorization)
		{
			this.promises.authorization.fulfill();
			this.promises.authorization = null;
		}
		this.authorized = true;
	}
	else
	{
		this.finishCall();
		this.setUiState(TelephonyUiState.FINISHED);
		if (e.code == 401 || e.code == 400 || e.code == 403 || e.code == 404 || e.code == 302)
		{
			this.setUiStateLabel(BX.message('IM_PHONE_401'));
		}
		else
		{
			this.setUiStateLabel(BX.message('IM_M_CALL_ERR'));
		}
	}
};

MobileTelephony.prototype._onCallConnected = function(e)
{
	this.log("_onCallConnected", e);

	this.setProgress('online');
	this.setUiStateLabel(BX.message('IM_M_CALL_ST_ONLINE'));
	this.callActive = true;
};

MobileTelephony.prototype._onCallDisconnected = function(e)
{
	this.log("_onCallDisconnected", e);
	this.finishCall();
	this.closeCallForm();
};

MobileTelephony.prototype._onCallFailed = function(e)
{
	this.log("_onCallFailed", e);

	let errorText = BX.message('IM_PHONE_END');
	if (this.phoneNumber == 911 || this.phoneNumber == 112)
	{
		errorText = BX.message('IM_PHONE_NO_EMERGENCY');
	}
	else if (e.code == 603)
	{
		errorText = BX.message('IM_PHONE_DECLINE');
	}
	else if (e.code == 380)
	{
		errorText = BX.message('IM_PHONE_ERR_SIP_LICENSE');
	}
	else if (e.code == 436)
	{
		errorText = BX.message('IM_PHONE_ERR_NEED_RENT');
	}
	else if (e.code == 438)
	{
		errorText = BX.message('IM_PHONE_ERR_BLOCK_RENT');
	}
	else if (e.code == 400)
	{
		errorText = BX.message('IM_PHONE_ERR_LICENSE');
	}
	else if (e.code == 401)
	{
		errorText = BX.message('IM_PHONE_401');
	}
	else if (e.code == 480 || e.code == 503)
	{
		errorText = BX.message('IM_PHONE_UNAVAILABLE');
	}
	else if (e.code == 484 || e.code == 404)
	{
		errorText = BX.message('IM_PHONE_INCOMPLETED');
	}
	else if (e.code == 402)
	{
		errorText = BX.message('IM_PHONE_NO_MONEY')+(this.isAdmin? ' '+BX.message('IM_PHONE_PAY_URL_NEW'): '');
	}
	else if (e.code == 486 && this.phoneRinging > 1)
	{
		errorText = BX.message('IM_M_CALL_ST_DECLINE');
	}
	else if (e.code == 486)
	{
		errorText = BX.message('IM_PHONE_ERROR_BUSY');
	}
	else if (e.code == 403)
	{
		errorText = BX.message('IM_PHONE_403');
	}

	this.finishCall();
	this.setUiState(TelephonyUiState.FINISHED);
	this.setUiStateLabel(errorText);
};

MobileTelephony.prototype._onCallProgressToneStart = function(e)
{
	this.log("_onCallProgressToneStart", e);
	this.phoneRinging++;
	this.setUiState(TelephonyUiState.WAITING);
	this.setUiStateLabel(BX.message('IM_PHONE_WAIT_ANSWER'));
};

MobileTelephony.prototype._onCallProgressToneStop = function(e)
{
	this.log("_onCallProgressToneStop", e);
};

MobileTelephony.prototype._onUiHangup = function(e)
{
	this.ui.cancelDelayedClosing();
	this.finishCall();
	this.closeCallForm();
};

MobileTelephony.prototype._onUiSpeakerphoneChanged = function(e)
{
	let params = e.params;
	let speakerState = params.selected;
	if (!this.call)
		return false;

	this.call.setUseLoudSpeaker(speakerState);
};

MobileTelephony.prototype._onUiMuteChanged = function(e)
{
	let params = e.params;
	let micState = params.selected;
	if (!this.call)
		return false;

	if (micState)
		this.call.muteMicrophone();
	else
		this.call.unmuteMicrophone();
};

MobileTelephony.prototype._onUiPauseChanged = function(e)
{
	let params = e.params;
	let holdState = params.selected;
	this.setCallHold(holdState);
};

MobileTelephony.prototype._onUiCloseClicked = function(e)
{
	this.finishCall();
	this.formShown = false;
};

MobileTelephony.prototype._onUiSkipClicked = function(e)
{
	this.sendSkip();
	this.finishCall();
	this.closeCallForm();
};

MobileTelephony.prototype._onUiAnswerClicked = function(e)
{
	this.ignoreAnswerSelf = true;
	this.ui.stopSound();
	if(this.isTransfer)
		this.answerTransferCall();
	else
		this.answerCall();
};

MobileTelephony.prototype._onUiNumpadButtonClicked = function(e)
{
	let key = e.params;

	if(this.call)
	{
		this.call.sendTone(key);
	}
};

MobileTelephony.prototype._onUiPhoneNumberReceived = function(data)
{
	if(Application.getApiVersion() >= 22)
	{
		var number = data.params;
		this.phoneCall(number);
	}
};

MobileTelephony.prototype._onUiCrmLinkClick = function(params)
{
	let entityType = params.entityType;
	let entityId = params.entityId;
	let crmUrl = getCrmShowPath(entityType, entityId);

	if(crmUrl)
	{
		PageManager.openPage({
			'url' : crmUrl,
			'bx24ModernStyle' : true
		});
		this.ui.rollUp();
	}
};

MobileTelephony.prototype._onCallInvite = function (params)
{
	this.log("_onCallInvite", params);
	if(this.callInit || this.callActive)
		return false;

	this.crmData = (params.CRM && params.CRM.FOUND) ? params.CRM : {};
	this.portalCall = params.portalCall === true;

	if (this.portalCall && params.portalCallData)
	{
		//params.callerId = this.BXIM.messenger.users[params.portalCallUserId].name;
		params.phoneNumber = '';

		this.crmData.FOUND = 'Y';
		this.crmData.CONTACT = {
			'NAME': params.portalCallData.users[params.portalCallUserId].name,
			'PHOTO': params.portalCallData.users[params.portalCallUserId].avatar
		};
	}

	this.callConfig = params.config? params.config: {};
	this.phoneCallTime = 0;

	this.ui.playSound({soundId: Sound.incoming});

	/*chatId, callId, callerId, companyPhoneNumber, isCallback*/
	params.isCallback = !!params.isCallback;
	params.isTransfer = !!params.isTransfer;

	this.phoneNumberUser =params.callerId;
	params.callerId = params.callerId.replace(/[^a-zA-Z0-9\.]/g, '');

	this.callInit = true;
	this.callActive = false;
	this.isIncoming = true;
	this.phoneCallTime = 0;
	this.callId = params.callId;
	this.phoneNumber = params.callerId;
	this.phoneParams = {};
	this.isTransfer = params.isTransfer;
	this.lineNumber = params.companyPhoneNumber;

	this.showCallForm({
		status : BX.message('IM_PHONE_INITIALIZATION'),
		state : TelephonyUiState.OUTGOING
	});

	let waitMethodName = this.isTransfer ? 'voximplant.call.waitTransfer' : 'voximplant.call.sendWait';
	BX.rest.callMethod(waitMethodName, {'CALL_ID' : params.callId, 'DEBUG_INFO': this.getDebugInfo()}).then((result) =>
	{
		let data = result.data();
		this.log('voximplant.call.sendWait data:', data);

		// call could be already finished by this moment
		if(!this.callInit && !this.callActive)
			return;

		if(data.SUCCESS)
		{
			this.setUiState(TelephonyUiState.INCOMING);
			this.setUiStateLabel(params.isCallback ? BX.message('IM_PHONE_INVITE_CALLBACK') : BX.message('IM_PHONE_INVITE'));
		}
		else
		{
			this.setUiState(TelephonyUiState.FINISHED);
			this.setUiStateLabel(BX.message("IM_PHONE_ERROR"));
		}
	}).catch((data) =>
	{
		let answer = data.answer;
		this.log(waitMethodName + ' error: ', answer);

		// call could be already finished by this moment
		if(!this.callInit && !this.callActive)
			return;

		this.setUiState(TelephonyUiState.FINISHED);
		if(answer.error === 'ERROR_NOT_FOUND')
			this.setUiStateLabel(BX.message("IM_M_CALL_ALREADY_FINISHED"));
		else if(answer.error === 'ERROR_WRONG_STATE')
			this.setUiStateLabel(BX.message("IM_M_CALL_ALREADY_ANSWERED"));
		else
			this.setUiStateLabel(BX.message("IM_PHONE_ERROR"));
	});
};

MobileTelephony.prototype._onPullEventInvite = function (params, extra)
{
	if(extra.server_time_ago >= eventTimeRange)
	{
		this.log("Call was started too long time ago");
		return;
	}

	if (this.callInit || this.callActive)
	{
		// todo: set and proceed busy status in b_voximplant_queue
		/*BX.MessengerCommon.phoneCommand('busy', {'CALL_ID' : params.callId});*/
		return false;
	}

	this._onCallInvite(params);
};

MobileTelephony.prototype._onPullEventAnswerSelf = function (params)
{
	if (this.ignoreAnswerSelf || this.callId != params.callId)
		return false;

	this.finishCall();
	this.ui.stopSound();
	this.closeCallForm();

	this.callInit = true;
	this.callId = params.callId;
};

MobileTelephony.prototype._onPullEventTimeout = function (params)
{
	if (this.callId != params.callId)
		return false;

	this.ui.stopSound();
	this.closeCallForm();
	this.finishCall();
};

MobileTelephony.prototype._onPullEventOutgoing = function (params, extra)
{
	if(extra.server_time_ago >= eventTimeRange)
	{
		this.log("Call was started too long time ago");
		return;
	}

	this.portalCall = params.portalCall === true;

	if (this.callInit && this.phoneNumber == params.phoneNumber)
	{
		if (!this.callId)
		{
			this.setProgress('connect');
			this.setUiStateLabel(BX.message('IM_PHONE_WAIT_ANSWER'));

			this.callConfig = params.config || {};
			this.callId = params.callId;
			this.phoneCallTime = 0;
			this.setCrmData(params.CRM);
		}

		if (this.portalCall && params.portalCallUserId)
		{
			this.setCrmData({
				FOUND: 'Y',
				CONTACT:
				{
					'NAME': params.portalCallData.users[params.portalCallUserId].name,
					'PHOTO': params.portalCallData.users[params.portalCallUserId].avatar
				}
			});
		}
	}
};

MobileTelephony.prototype._onPullEventStart = function (params)
{
	// not sure if we need this handler in the mobile telephony at all.

	if (this.callId != params.callId)
		return false;

	this.ui.startTimer();
	this.ui.stopSound();
	this.callActive = true;

	if (params.CRM)
		this.setCrmData(params.CRM);
};

MobileTelephony.prototype._onPullEventHold = function(params)
{
	if (this.callId == params.callId)
	{
		this.phoneHolded = true;
	}
};

MobileTelephony.prototype._onPullEventUnHold = function(params)
{
	this.phoneHolded = false;
};

MobileTelephony.prototype._onPullEventUpdateCrm = function (params)
{
	if (this.callId == params.callId && params.CRM)
	{
		this.setCrmData(params.CRM);
	}
};

MobileTelephony.prototype._onPullEventInviteTransfer = function (params, extra)
{
	if(extra.server_time_ago >= eventTimeRange)
	{
		this.log("Call was started too long time ago");
		return;
	}
	if (this.callInit || this.callActive)
		return false;

	if (params.CRM && params.CRM.FOUND)
	{
		this.crmData = params.CRM;
	}

	this._onCallInvite({
		chatId: params.chatId,
		callId: params.callId,
		callerId: params.callerId,
		companyPhoneNumber: params.phoneNumber,
		config: params.config,
		CRM: params.CRM,
		isTransfer: true
	});
};

MobileTelephony.prototype._onPullEventCancelTransfer = function (params)
{
	if (this.callId == params.callId && !this.callSelfDisabled)
	{
		this.callInit = false;
		this.ui.stopSound();
		this.sendSkip();
		this.finishCall();
		this.closeCallForm();
	}
};

MobileTelephony.prototype._onPullEventDeclineTransfer = function (params)
{
	if (this.callId == params.callId)
	{
		this.errorInviteTransfer();
	}
};

MobileTelephony.prototype._onPullEventCompleteTransfer = function (params)
{
	if (this.callId == params.callId)
	{
		if (params.transferUserId != this.BXIM.userId || this.isMobile())
		{
			this.successInviteTransfer();
		}
		else
		{
			this.isTransfer = false;
			BX.localStorage.set('vite', false, 1);

			if (params.callDevice == 'PHONE')
			{
				this.ui.stopSound();

				if (this.isMobile())
				{
					this.BXIM.messenger.openMessenger(this.BXIM.messenger.currentTab);
				}
				this.phoneCallDevice = 'PHONE';
				this.phoneOnCallConnected();
			}
			if (params.CRM)
			{
				this.setCrmData(params.CRM);
			}
		}
	}
};

MobileTelephony.prototype._onPullEventPhoneDeviceActive = function (params)
{
	//nop
};

MobileTelephony.prototype._onPullEventChangeDefaultLineId = function (params)
{
	this.defaultLineId = params.defaultLineId;
	if(!this.lines.hasOwnProperty(this.defaultLineId))
	{
		this.lines[this.defaultLineId] = params.line;
	}
};

MobileTelephony.prototype._onPullEventReplaceCallerId = function (params)
{
	let callTitle = BX.message('IM_PHONE_CALL_TRANSFER').replace('#PHONE#', params.callerId);
	this.setCallOverlayTitle(callTitle);
	if (params.CRM)
	{
		this.setCrmData(params.CRM);
	}
};

MobileTelephony.prototype.getDebugInfo = function()
{
	return {
		isMobile: 'Y',
		callInit: this.callInit ? 'Y' : 'N',
		callActive: this.callActive ? 'Y' : 'N',
		appVersion: Application.getAppVersion(),
		apiVersion: Application.getApiVersion(),
		buildVersion: Application.getBuildVersion()
	}
};

MobileTelephony.prototype.phoneCorrect = function(number)
{
	number = number.toString().trim();

	if (number.substr(0, 2) == '+8' && number.length > 10)
	{
		number = '008'+number.substr(2);
	}
	number = number.replace(/[^0-9\*]/g, '');

	if (number.substr(0, 2) == '80' || number.substr(0, 2) == '81' || number.substr(0, 2) == '82')
	{
	}
	else if (number.substr(0, 2) == '00' && number.length >= 9)
	{
		number = number.substr(2);
	}
	else if (number.substr(0, 3) == '011' && number.length >= 10)
	{
		number = number.substr(3);
	}
	else if (number.substr(0, 1) == '8' && number.length >= 11)
	{
		number = '7'+number.substr(1);
	}
	else if (number.substr(0, 1) == '0' && number.length >= 8)
	{
		number = number.substr(1);
	}

	return number;
};

MobileTelephony.prototype.log = function()
{
	if(this.debug)
	{
		console.log.apply(null, arguments);
	}
};

MobileTelephony.prototype.logged = function(cb)
{
	let self = this;
	return function()
	{
		let params = [cb.name].concat(arguments);
		console.log.apply(null, params);
		cb.apply(self, arguments);
	}
};

var TelephonyCall = function (number, useVideo, callParams, mobileVoximplant, callId)
{
	this.callState = CallState.DISCONNECTED;
	this.listeners = {};
	this.mobileVoximplant = mobileVoximplant;
	this.params = {
		callId: callId ? callId : null,
		phoneNumber: number,
		useVideo: ((typeof useVideo == "undefined") ? false : useVideo),
		callParams: JSON.stringify(callParams),
		onCreateCallback: this.onCreate.bind(this),
		listeners: {
			"onCallConnected": this.onConnected.bind(this),
			"onCallDisconnected": this.onDisconnected.bind(this),
			"onCallFailed": this.onFailed.bind(this),
			"onProgressToneStart": this.onProgressToneStart.bind(this),
			"onProgressToneStop": this.onProgressToneStop.bind(this)
		}
	}
};

TelephonyCall.prototype.addEventListener = function (eventName, handler)
{
	this.listeners[eventName] = handler;
};

TelephonyCall.prototype.onCreate = function (result)
{
	this.params.callId = result.callId;
};

TelephonyCall.prototype.start = function ()
{
	if (this.params.callId !== null)
		return;

	this.mobileVoximplant.createCallAndStart(this.params);
};

TelephonyCall.prototype.removeEventListener = function (eventName)
{
	delete this.listeners[eventName];
};

TelephonyCall.prototype.answer = function ()
{
	if (this.params.callId === null)
		return;

	this.mobileVoximplant.answer(this.params);
};

TelephonyCall.prototype.hangup = function ()
{
	this.mobileVoximplant.hangup(this.params);
};

TelephonyCall.prototype.decline = function ()
{
	this.mobileVoximplant.decline(this.params);
};

TelephonyCall.prototype.id = function ()
{
	return this.params.callId
};

TelephonyCall.prototype.state = function ()
{
	return this.callState;
};

TelephonyCall.prototype.muteMicrophone = function ()
{
	this.mobileVoximplant.setMute({
		mute: true
	});
};

TelephonyCall.prototype.unmuteMicrophone = function ()
{
	this.mobileVoximplant.setMute({
		mute: false
	});
};
/**
 * Switches off/on loud speaker
 * @param {boolean} enabled
 */
TelephonyCall.prototype.setUseLoudSpeaker = function (enabled)
{
	this.mobileVoximplant.setUseLoudSpeaker({
		enabled: (typeof enabled == "boolean" ? enabled : false)
	});
};

/**
 * Sends instant message within this call
 * @param {object} message
 * @param {object} [headers] headers of message
 */
TelephonyCall.prototype.sendMessage = function (message, headers)
{
	let messageObject = {
		callId: this.id(),
		text: JSON.stringify(message),
		headers: (typeof headers != "object") ? {} : headers
	};

	this.mobileVoximplant.sendMessage(messageObject);
};
/**
 * Sends DTMF digit in this call.
 * @param {string} digit [0-9|*|#]
 */
TelephonyCall.prototype.sendTone = function (digit)
{
	let correctDigit = digit;
	if(correctDigit.search(/^[\d*#]$/) === 0)
	{
		/* DTMF code can be 0-9 for 0-9, 10 for * and 11 for # */
		if(correctDigit === '*')
			correctDigit = '10';
		else if(correctDigit === '#')
			correctDigit = '11';

		this.mobileVoximplant.sendDTMF({
			callId: this.id(),
			digit: correctDigit
		});
	}
};

TelephonyCall.prototype.onDisconnected = function ()
{
	this.callState = CallState.DISCONNECTED;
	this.executeCallback(CallEvent.Disconnected);
};

TelephonyCall.prototype.onConnected = function ()
{
	this.callState = CallState.CONNECTED;
	this.executeCallback(CallEvent.Connected);
};

TelephonyCall.prototype.onFailed = function (data)
{
	this.callState = CallState.DISCONNECTED;
	this.executeCallback(CallEvent.Failed, data);
};

TelephonyCall.prototype.onProgressToneStart = function ()
{
	this.callState = CallState.CONNECTING;
	this.executeCallback(CallEvent.ProgressToneStart);
};

TelephonyCall.prototype.onProgressToneStop = function ()
{
	this.executeCallback(CallEvent.ProgressToneStop);
};

TelephonyCall.prototype.executeCallback = function (eventName, data)
{
	if (typeof this.listeners[eventName] == "function")
	{
		return this.listeners[eventName](data);
	}
};


// endregion Mobile telephony

// region Initialization

var mwebrtc = new MobileWebrtc();
var mtelephony = new MobileTelephony();

// endregion Initialization