{"version":3,"file":"logic.min.js","sources":["logic.js"],"names":["BX","namespace","Tasks","Component","TasksWidgetButtonsTask","Util","Widget","extend","sys","code","options","goToListOnDelete","methods","construct","this","callConstruct","vars","data","option","can","publicMode","overtime","buttonActionEnabled","time","TIME_ELAPSED","parseInt","bindEvents","getDayPlan","bindDelegateControl","passCtx","onMenuOpen","onButtonPressed","node","PopupMenu","show","getTaskMenu","closeByEsc","action","type","isNotEmptyString","doDynamicAction","getQuery","instances","query","Query","autoExec","dayplan","taskData","clone","taskId","ID","DayPlan","registerDispatcher","id","bindEvent","delegate","showTimerConfirm","onTaskTimerToggled","onTaskPlanToggled","onTaskTimerTick","getUserSelector","selector","Integration","Socialnetwork","NetworkSelector","scope","control","mode","useSearch","parent","onSelectorItemSelected","item","close","userId","doMenuAction","menu","e","window","location","confirmDelete","message","then","bind","open","popupWindow","args","togglePanelActivity","force","addToPlan","doDynamicTaskAction","reFetchTaskData","add","toLowerCase","errors","checkHasErrors","OPERATION","fireGlobalTaskEvent","parameters","ENTITY_SELECT","reload","stopTimer","updateTaskData","RESULT","DATA","updatePlanner","ACTION","DAYPLAN.ADD","destroy","mergeEx","updateButtons","eventTaskData","STAY_AT_PAGE","map","timer-start","TIMER_IS_RUNNING_FOR_CURRENT_USER","timer-pause","timer-running","timer-visible","pause","PAUSE","start","START","complete","COMPLETE","approve","APPROVE","disapprove","DISAPPROVE","edit","EDIT","more-button","RENEW","toggleCSSMap","partialData","estimate","TIME_ESTIMATE","isNaN","changeCSSFlag","innerHTML","formatTimeAmount","way","addToData","previousTask","body","replace","title","util","htmlspecialchars","confirm","result","push","text","className","onclick","DELEGATE","DEFER","REMOVE","delta","call"],"mappings":"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"}