{"version":3,"sources":["core_autosave.js"],"names":["window","BX","CAutoSave","top","params","this","FORM_NAME","form","FORM_MARKER","form_marker","FORM_ID","form_id","PERIOD","period","RESTORE_DATA","TIMERS","bInited","bRestoreInProgress","DISABLE_STANDARD_NOTIFY","NOTIFY_CONTEXT","ready","defer","Prepare","garbage","delegate","Clear","type","isNotEmptyString","formMarker","name","addCustomEvent","proxy","Reset","prototype","i","isString","FORM","document","forms","isDomNode","BXAUTOSAVE","bind","ClearTimers","elements","length","RegisterInput","setTimeout","_PrepareAfter","inp","Init","UnRegisterInput","unbind","onCustomEvent","id","Math","random","Restore","o","_NotifyContext","Notify","message","util","urlencode","clearTimeout","TimerHandler","setInterval","Save","clearInterval","isNodeInDom","j","el","data","autosave_id","form_data","n","v","t","toLowerCase","checked","value","substring","options","selected","push","indexOf","_encodeName","ajax","post","thurly_sessid","_Save","clicker","_decodeData","undefined","isArray","shift","bChange","fireEvent","q","in_array","hideNotify","parentNode","WindowManager","Get","adminPanel","admin","panel","exec","replace","str_pad_left","charCodeAt","toString","_decodeName","String","fromCharCode","parseInt","d"],"mappings":"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","file":""}