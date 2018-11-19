{"version":3,"file":"scrollpane.min.js","sources":["scrollpane.js"],"names":["BX","namespace","Tasks","Util","ScrollPane","Widget","extend","options","controlBind","sys","code","methods","construct","this","callConstruct","vars","prevScroll","bindEvents","bindControlThis","filterWheel","onScrollChanged","throttle","bind","window","delegate","force","pane","control","scroll","scrollTop","fireEvent","bHeight","clientHeight","pHeight","e","wData","getWheelData","jam","scrollHeight","PreventDefault","eventCancelBubble","append","html","cont","vial","create","type","isNotEmptyString","innerHTML","prepend","clear","destroy","ScrollPanePopup","popupParameters","windowId","maxHeight","bindTo","popupId","option","Math","floor","random","getPopup","instances","baseParams","zIndex","content","scope","autoHide","closeByEsc","params","k","hasOwnProperty","PopupWindow","node","setBindElement","show","isElementNode","style","hide","close","isShown","callMethod","arguments"],"mappings":"AAAAA,GAAGC,UAAU,aAEb,UAAUD,IAAGE,MAAMC,KAAKC,YAAc,YACtC,CACCJ,GAAGE,MAAMC,KAAKC,WAAaJ,GAAGE,MAAMC,KAAKE,OAAOC,QAC/CC,SACCC,YAAa,SAEdC,KACCC,KAAM,cAEPC,SAECC,UAAW,WAEVC,KAAKC,cAAcd,GAAGE,MAAMC,KAAKE,OAEjCQ,MAAKE,KAAKC,WAAa,KAEvBH,MAAKI,cAGNA,WAAY,WAEXJ,KAAKK,gBAAgB,OAAQ,aAAcL,KAAKM,YAEhDN,MAAKO,gBAAkBpB,GAAGqB,SAASR,KAAKO,gBAAiB,GAAIP,KAE7DA,MAAKK,gBAAgB,OAAQ,SAAUL,KAAKO,gBAC5CP,MAAKK,gBAAgB,OAAQ,aAAcL,KAAKO,gBAChDpB,IAAGsB,KAAKC,OAAQ,SAAUvB,GAAGwB,SAASX,KAAKO,gBAAiBP,MAE5DA,MAAKO,mBAGNA,gBAAiB,SAASK,GAEzB,GAAIC,GAAOb,KAAKc,QAAQ,OACxB,KAAID,EACJ,CACC,OAGD,GAAIE,GAASF,EAAKG,SAElB,IAAGJ,GAASZ,KAAKE,KAAKC,aAAe,OAASH,KAAKE,KAAKC,aAAeY,EACvE,CACC,IAAIA,EACJ,CACCf,KAAKiB,UAAU,eAGhB,GAAIC,GAAUlB,KAAKc,QAAQ,QAAQK,YACnC,IAAIC,GAAUP,EAAKM,YAEnB,IAAGJ,EAASK,GAAWF,EACvB,CACClB,KAAKiB,UAAU,mBAIjBjB,KAAKE,KAAKC,WAAaY,GAGxBT,YAAa,SAASe,GAErB,GAAIC,GAAQnC,GAAGoC,aAAaF,EAC5B,IAAIR,GAAOb,KAAKc,QAAQ,OACxB,IAAIU,GAAM,KAEV,IAAGF,EAAQ,GAAKT,EAAKG,WAAa,EAClC,CACCQ,EAAM,KAGP,GAAGF,EAAQ,GAAMT,EAAKG,WAAaH,EAAKY,aAAeZ,EAAKM,aAC5D,CACCK,EAAM,KAGP,GAAGA,EACH,CACCrC,GAAGuC,eAAeL,EAClBlC,IAAGwC,kBAAkBN,EACrB,OAAO,SAITO,OAAQ,SAASC,GAEhB,GAAIC,GAAO9B,KAAKc,QAAQ,YACxB,IAAGgB,EACH,CACC,GAAGD,EACH,CACC,GAAIE,GAAO5C,GAAG6C,OAAO,MAErB,IAAG7C,GAAG8C,KAAKC,iBAAiBL,GAC5B,CACCE,EAAKI,UAAYN,CACjB1C,IAAGyC,OAAOG,EAAMD,GAGjB9B,KAAKO,gBAAgB,SAIxB6B,QAAS,SAASP,GAGjB7B,KAAKO,gBAAgB,OAItB8B,MAAO,WAEN,GAAIP,GAAO9B,KAAKc,QAAQ,YACxB,IAAGgB,EACH,CACCA,EAAKK,UAAY,GAElBnC,KAAKE,KAAKC,WAAa,OAGxBmC,QAAS,gBAOZ,SAAUnD,IAAGE,MAAMC,KAAKiD,iBAAmB,YAC3C,CACCpD,GAAGE,MAAMC,KAAKiD,gBAAkBpD,GAAGE,MAAMC,KAAKC,WAAWE,QACxDC,SACC8C,mBACAC,SAAU,GACVC,UAAW,KAEZ5C,SAECC,UAAW,WAEVC,KAAKC,cAAcd,GAAGE,MAAMC,KAAKC,WAEjCS,MAAKE,KAAKyC,OAAS,IACnB3C,MAAKE,KAAK0C,QACTzD,GAAG8C,KAAKC,iBAAiBlC,KAAK6C,OAAO,aACrC7C,KAAK6C,OAAO,YACZ7C,KAAKH,OAAO,UAAUiD,KAAKC,MAAMD,KAAKE,SAAS,MAGjDC,SAAU,WAET,SAAUjD,MAAKkD,UAAUxC,QAAU,YACnC,CACC,GAAIyC,IACHC,OAAS,KACTC,QAAUrD,KAAKsD,QACfC,SAAa,KACbC,WAAa,KAEd,IAAIC,GAASzD,KAAK6C,OAAO,kBACzB,KAAI,GAAIa,KAAKD,GACb,CACC,GAAGA,EAAOE,eAAeD,GACzB,CACCP,EAAWO,GAAKD,EAAOC,IAIzB1D,KAAKkD,UAAUxC,OAAS,GAAIvB,IAAGyE,YAC9B5D,KAAKE,KAAK0C,QACV5C,KAAKE,KAAKyC,OACVQ,GAIF,MAAOnD,MAAKkD,UAAUxC,QAGvBiC,OAAQ,SAASkB,GAEhB7D,KAAKE,KAAKyC,OAASkB,CACnB,IAAG7D,KAAKkD,UAAUxC,OAClB,CACCV,KAAKkD,UAAUxC,OAAOoD,eAAeD,GAGtC,MAAO7D,OAGR+D,KAAM,SAASF,GAEd,GAAG1E,GAAG8C,KAAK+B,cAAcH,GACzB,CACC7D,KAAK2C,OAAOkB,GAGb7D,KAAKc,QAAQ,QAAQmD,MAAMvB,UAAY1C,KAAK6C,OAAO,aAAa,IAEhE7C,MAAKiD,WAAWc,MAChB/D,MAAKO,gBAAgB,KAErB,OAAOP,OAGRkE,KAAM,WAEL,GAAGlE,KAAKkD,UAAUxC,OAClB,CACCV,KAAKiD,WAAWkB,QAGjB,MAAOnE,OAGRO,gBAAiB,SAASK,GAEzB,GAAGZ,KAAKkD,UAAUxC,QAAUV,KAAKkD,UAAUxC,OAAO0D,UAClD,CACCpE,KAAKqE,WAAWlF,GAAGE,MAAMC,KAAKC,WAAY,kBAAmB+E,aAI/DhC,QAAS,WAERtC,KAAKkE,MAEL,IAAGlE,KAAKkD,UAAUxC,OAClB,CACCV,KAAKkD,UAAUxC,OAAOyD,OACtBnE,MAAKkD,UAAUxC,OAAO4B,SACtBtC,MAAKkD,UAAUxC,OAAS"}