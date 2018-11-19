{"version":3,"file":"logic.min.js","sources":["logic.js"],"names":["BX","namespace","Tasks","Component","UserFieldPanel","extend","sys","code","methods","construct","this","callConstruct","instances","items","ItemSet","scope","data","option","itemFx","parent","bindEvent","delegate","onItemSave","onItemHide","onItemDelete","saveState","debounce","saveStateInstant","redrawButtons","bindEvents","bindControlPassCtx","showAddPopup","showActionPopup","showUnHideFieldPopup","bindDelegateControl","jamEnter","control","e","Util","isEnter","eventReturnFalse","haveHidden","allHidden","each","item","STATE","D","changeCSSFlag","openAddForm","menu","popupWindow","close","options","p","id","ENTITY_CODE","callRemote","parameters","INPUT_PREFIX","RELATED_ENTITIES","then","result","isSuccess","fulfill","getData","reject","getErrors","user","useDelete","MANAGE","IS_SUPER","title","message","params","ctx","isDisposable","buttonSet","text","type","default","confirm","setItemStateDisplay","alert","bind","way","instant","get","setItemSortBefore","beforeItem","index","i","push","value","parseInt","sort","S","a","b","itemValue","beforeItemValue","j","k","hasOwnProperty","dropAll","state","packState","entityCode","initDragNDrop","dragNDrop","dd","DragAndDrop","createFlying","node","getItemByNode","getNodeByTemplate","autoMarkItemAfter","autoMarkZoneTopBottom","bindDropZone","onDragNDropItemRelocatedBefore","onDragNDropItemRelocatedAfter","bindDragNDropNode","neigbours","cancelAutoResolve","disappear","appear","before","bindNode","handle","controlAll","registerItem","doMenuAction","action","layout","innerHTML","enabled","getItem","Promise","NO_FIELD_HTML","entityId","html","getMessages","join","setFieldHtml","unHideField","isNotEmptyString","showItem","unHideMenu","hide","onclick","passCtx","unshift","PopupMenu","show","angle","position","offsetLeft","offsetTop","canManage","B24","licenseInfoPopup","types","isPlainObject","closeByEsc","subInstance","exportItemData","count","getHTMLByTemplate","LABEL","ID","LABEL_EXT","MANDATORY","STAR_INVISIBLE","ScrollPanePopup","maxHeight","popupParameters","noAllPaddings","controlBind","preRendered","itemAppearFxSpeed","itemDisappearFxSpeed","getItemClass","Item","bindItemActions","bindOnItem","hideItem","enterEditItem","leaveEditItem","leaveEditItemNew","saveItem","saveItemNew","itemLabelChanged","fireEvent","deleteItem","toggleEditMode","initForm","newItem","isLoading","setLoading","hideErrors","getFormData","newData","update","errors","showErrors","FIELD_HTML","MULTIPLE","useAppear","label","toUpperCase","USER_TYPE_ID","DISPLAY","createItem","VALUE","REQUIRED","EDIT","INVISIBLE","DEFACEABLE","DISPLAY_MULTIPLE","append","isShown","noFx","formPlace","fadeSlideToggleByClass","multipleCtrl","checked","disableMultiple","disabled","parentNode","focus","clone","itemData","redraw","util","htmlspecialchars","setAttribute","callMethod","arguments","vars","actionInProgress","loading","delay","addClass","removeClass","call","cancel","replace","RegExp","hideByClass","showByClass","prepareData"],"mappings":"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"}