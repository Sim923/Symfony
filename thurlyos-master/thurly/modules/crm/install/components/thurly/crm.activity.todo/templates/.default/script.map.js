{"version":3,"sources":["script.js"],"names":["BX","CrmActivityTodo","settings","this","_ccontainer","ccontainer","_citem","citem","_clink","clink","_ccheck","ccheck","_cbuttoncancel","cbuttoncancel","_ccheckprefix","ccheckprefix","_ajaxPath","ajax_path","_ajaxPlannerPath","ajax_planner_path","message","_dialogId","_popup","_activityId","activityLink","findChild","class","tag","i","length","bind","delegate","_clickTitleHandler","activityCheck","_clickCheckHandler","prototype","_getParent","proxy","findParent","_showPopup","title","events","PopupWindow","window","body","offsetLeft","lightShadow","closeIcon","titleBar","content","create","html","draggable","closeByEsc","contentColor","overlay","backgroundColor","opacity","setContent","setTitleBar","show","_loadActivity","_this","ajax","post","sessid","thurly_sessid","ajax_action","activity_id","data","adjustPosition","additionalSwitcher","_getNodeByRole","additionalFields","fieldCompleted","toggleClass","remove","setButtons","PopupWindowButtonLink","text","className","click","popupWindow","close","container","name","querySelector","e","proxy_context","view","CrmActivityEditor","items","viewActivity","onAfterPopupShow","onPopupClose","destroy","PreventDefault","checked","context","parent","loadJSON","action","id","ownerid","ownertypeid","completed","error","alert","onCustomEvent","parseInt","disabled","addClass","_self"],"mappings":"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","file":""}