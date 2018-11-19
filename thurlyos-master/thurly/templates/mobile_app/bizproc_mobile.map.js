{"version":3,"sources":["bizproc_mobile.js"],"names":["BX","BizProcMobile","doTask","parameters","callback","silent","thurly_sessid","app","showPopupLoader","text","ajax","method","dataType","url","message","data","onsuccess","json","hidePopupLoader","ERROR","alert","title","BXMobileApp","onCustomEvent","onfailure","reload","openTaskPage","taskId","event","target","tagName","toLowerCase","anchorNode","findParent","tag","className","hasClass","loadPageBlank","unique","renderLogMessage","logElement","newContent","updateId","wrapper","parentNode","innerHTML","querySelector","tasks","JSON","parse","getAttribute","userId","statusWaiting","statusYes","statusNo","statusOk","statusCancel","userStatus","getUserFromTask","task","i","l","USERS","length","user","STATUS","ID","btn","findChild","style","display","taskBlock","userStatusCls","userStatusBlock","statusBlock","setAttribute","renderLogMessages","scope","workflowId","newLogContent","items","querySelectorAll","rendered","itemWorkflowId","toString","itemContent","loadLogMessageCallback","WORKFLOW_ID","HTML","parseInt","Math","random","document","renderFacePhoto","users","displayedUser","onload","src","showDatePicker","format","input","type","pickerParams","value","d","Date","siteFormat","formatted","formatDate","UI","DatePicker","setParams","show","PreventDefault"],"mappings":"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","file":""}