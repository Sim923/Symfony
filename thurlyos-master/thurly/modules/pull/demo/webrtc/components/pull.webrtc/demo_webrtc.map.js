{"version":3,"file":"demo_webrtc.min.js","sources":["demo_webrtc.js"],"names":["window","YourCompanyPrefix","webrtc","params","this","parent","constructor","apply","arguments","debug","callWindowBeforeUnload","placeholder","signalingLink","ready","BX","addCustomEvent","delegate","command","log","senderId","JSON","stringify","callInit","ajax","url","method","dataType","timeout","data","COMMAND","USER_ID","sessid","thurly_sessid","initiator","callVideo","callUserId","callInitUserId","drawAnswerControls","startGetUserMedia","drawDeclineControls","callDecline","connected","callActive","clearTimeout","pcConnectTimeout","initPeerConnectionTimeout","pc","close","pcStart","callStreamMain","callStreamUsers","initPeerConnection","signalingPeerData","peer","garbage","callCommand","inheritWebrtc","prototype","video","audio","onbeforeunload","PULL","tryConnectDelay","message","onUserMediaSuccess","stream","result","attachMediaStream","interfaceVideoSelf","callStreamSelf","muted","addClass","onUserMediaError","error","setLocalAndSend","userId","desc","PEER","onRemoteStreamAdded","event","setMainVideo","interfaceVideoMain","volume","play","onRemoteStreamRemoved","onIceCandidate","candidates","peerConnectionError","peerConnectionReconnect","onsuccess","deleteEvents","pause","removeClass","src","callInvite","interfaceUserId","value","parseInt","drawWaitControls","callAnswer","send","drawInviteControls","async","signalingReady","drawInterface","interface","create","props","className","children","attrs","autoplay","interfaceVideoControls","html","innerHTML","appendChild","adjust","events","click","type"],"mappings":"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"}