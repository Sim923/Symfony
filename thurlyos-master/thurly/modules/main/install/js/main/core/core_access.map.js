{"version":3,"file":"core_access.min.js","sources":["core_access.js"],"names":["BX","window","Access","bInit","waitDiv","waitPopup","bDialogLoaded","selectedProvider","obSelected","obCnt","__providers_cnt","obAlreadySelected","obSelectedBind","showSelected","popup","callback","obProviderNames","arParams","Init","ready","delegate","PopupWindowManager","create","autoHide","zIndex","offsetLeft","offsetTop","draggable","restrict","closeByEsc","titleBar","message","contentColor","contentNoPaddings","closeIcon","buttons","PopupWindowButton","text","className","events","click","SaveLRU","SaveSelected","this","popupWindow","close","PopupWindowButtonLink","content","onAfterPopupShow","showWait","contentContainer","ajax","post","lang","site_id","result","setContent","closeWait","code","AddSelection","onCustomEvent","onPopupClose","ClearSelection","ShowForm","bind","params","WindowManager","GetZIndex","show","div","PopupWindow","lightShadow","props","setBindElement","height","offsetHeight","width","offsetWidth","setOffset","parseInt","SelectProvider","id","style","display","provider","ob","clone","appendChild","html","name","innerHTML","RemoveSelection","item","parentNode","removeChild","LRU","mode","sessid","thurly_sessid","pr","SetSelected","obSel","DeleteSelected","GetProviderName","GetProviderPrefix","prefixes","i","expr","RegExp","test"],"mappings":"CAAA,WACA,GAAIA,GAAKC,OAAOD,EAChB,IAAGA,EAAGE,OACL,MAEDF,GAAGE,QAEFC,MAAO,MACPC,QAAS,KACTC,UAAW,KACXC,cAAe,MACfC,iBAAkB,GAClBC,cACAC,OAAQC,gBAAiB,GACzBC,qBACAC,kBACAC,aAAc,MACdC,MAAO,KACPC,SAAU,KACVC,mBACAC,YAGDjB,GAAGE,OAAOgB,KAAO,SAASD,GAEzB,GAAGA,EACFjB,EAAGE,OAAOe,SAAWA,CAEtB,IAAGjB,EAAGE,OAAOC,MACZ,MAEDH,GAAGE,OAAOC,MAAQ,IAElBH,GAAGmB,MAAMnB,EAAGoB,SAAS,WAEpBpB,EAAGE,OAAOY,MAAQd,EAAGqB,mBAAmBC,OAAO,eAAgB,MAC9DC,SAAU,MACVC,OAAQ,EACRC,WAAY,EACZC,UAAW,EACXC,WAAYC,SAAS,MACrBC,WAAY,KACZC,SAAU9B,EAAG+B,QAAQ,mBACrBC,aAAe,QACfC,kBAAoB,KACpBC,UAAW,KACXC,SACC,GAAInC,GAAGoC,mBACNC,KAAOrC,EAAG+B,QAAQ,oBAClBO,UAAY,6BACZC,QAAWC,MAAQ,WAElBxC,EAAGE,OAAOuC,SAEVzC,GAAGE,OAAOwC,cAEV,IAAG1C,EAAGE,OAAOa,SACZf,EAAGE,OAAOa,SAASf,EAAGE,OAAOM,WAE9BmC,MAAKC,YAAYC,YAInB,GAAI7C,GAAG8C,uBACNT,KAAMrC,EAAG+B,QAAQ,mBACjBO,UAAW,kCACXC,QAAUC,MAAQ,WAEjBG,KAAKC,YAAYC,aAIpBE,QAAS,uCACTR,QACCS,iBAAkB,WAEjB,IAAIhD,EAAGE,OAAOI,cACd,CACCN,EAAGE,OAAO+C,SAASN,KAAKO,iBACxBlD,GAAGmD,KAAKC,KACP,mCAECC,KAAMrD,EAAG+B,QAAQ,eACjBuB,QAAStD,EAAG+B,QAAQ,YAAc,GAClCd,SAAUjB,EAAGE,OAAOe,UAErBjB,EAAGoB,SAAS,SAASmC,GAEpBZ,KAAKa,WAAWD,EAChBvD,GAAGE,OAAOuD,WACVzD,GAAGE,OAAOI,cAAgB,IAE1B,IAAIN,EAAGE,OAAOW,aACd,CACC,IAAI,GAAI6C,KAAQ1D,GAAGE,OAAOS,kBAC1B,CACC,SAAWX,GAAGE,OAAOS,kBAAkB+C,IAAU,SACjD,CACC1D,EAAGE,OAAOyD,aAAa3D,EAAGE,OAAOS,kBAAkB+C,QAKvDf,WAIF,CACC,GAAI3C,EAAGE,OAAOW,aACd,CACC,IAAI,GAAI6C,KAAQ1D,GAAGE,OAAOS,kBAC1B,CACC,SAAWX,GAAGE,OAAOS,kBAAkB+C,IAAU,SACjD,CACC1D,EAAGE,OAAOyD,aAAa3D,EAAGE,OAAOS,kBAAkB+C,OAKvD1D,EAAG4D,cAAc5D,EAAGE,OAAQ,wBAE7B2D,aAAc,WAEb7D,EAAGE,OAAO4D,sBAKXnB,OAGJ3C,GAAGE,OAAO6D,SAAW,SAAS9C,GAE7B,IAAIA,EAAS+C,KACZ/C,EAAS+C,KAAO,MACjBhE,GAAGE,OAAO8D,KAAO/C,EAAS+C,IAC1BhE,GAAGE,OAAOW,aAAeI,EAASJ,eAAiB,IAEnD,IAAGb,EAAGE,OAAOU,eAAeK,EAAS+C,MACpChE,EAAGE,OAAOS,kBAAoBX,EAAGE,OAAOU,eAAeK,EAAS+C,UAEhEhE,GAAGE,OAAOS,oBAEXX,GAAGE,OAAOa,SAAWE,EAASF,QAC9Bf,GAAGE,OAAOY,MAAMmD,OAAOzC,OAAUxB,EAAGkE,cAAelE,EAAGkE,cAAcC,YAAc,CAClFnE,GAAGE,OAAOY,MAAMsD,OAGjBpE,GAAGE,OAAO+C,SAAW,SAASoB,GAE7BrE,EAAGE,OAAOE,QAAUJ,EAAGE,OAAOE,SAAWiE,CACzCA,GAAMrE,EAAGqE,GAAOrE,EAAGE,OAAOE,QAE1B,KAAKJ,EAAGE,OAAOG,UACf,CACCL,EAAGE,OAAOG,UAAY,GAAIL,GAAGsE,YAAY,UAAWD,GACnD9C,SAAU,KACVgD,YAAa,KACb/C,OAASxB,EAAGkE,cAAelE,EAAGkE,cAAcC,YAAc,EAC1DpB,QAAS/C,EAAGsB,OAAO,OAAQkD,OAAQlC,UAAW,mBAIhD,CACCtC,EAAGE,OAAOG,UAAUoE,eAAeJ,GAGpC,GAAIK,GAASL,EAAIM,aAAcC,EAAQP,EAAIQ,WAC3C,IAAIH,EAAS,GAAKE,EAAQ,EAC1B,CACC5E,EAAGE,OAAOG,UAAUyE,WACnBpD,WAAYqD,SAASL,EAAO,EAAE,IAC9BjD,WAAYsD,SAASH,EAAM,EAAE,KAG9B5E,GAAGE,OAAOG,UAAU+D,OAGrB,MAAOpE,GAAGE,OAAOG,UAGlBL,GAAGE,OAAOuD,UAAY,WAErB,GAAGzD,EAAGE,OAAOG,UACZL,EAAGE,OAAOG,UAAUwC,QAGtB7C,GAAGE,OAAO8E,eAAiB,SAASC,GAEnC,GAAGjF,EAAGE,OAAOK,kBAAoB,GACjC,CACCP,EAAG,cAAcA,EAAGE,OAAOK,kBAAkB+B,UAAY,wBACzDtC,GAAG,mBAAmBA,EAAGE,OAAOK,kBAAkB2E,MAAMC,QAAU,OAEnEnF,EAAG,cAAciF,GAAI3C,UAAY,wDACjCtC,GAAG,mBAAmBiF,GAAIC,MAAMC,QAAU,EAC1CnF,GAAGE,OAAOK,iBAAmB0E,CAE7BjF,GAAG4D,cAAc5D,EAAGE,OAAQ,qBAAsBkF,SAAYH,KAG/DjF,GAAGE,OAAOyD,aAAe,SAAS0B,GAEjC,IAAIA,EAAGD,SACP,CACC,OAGD,IAAIpF,EAAGE,OAAOM,WAAW6E,EAAGD,UAC5B,CACCpF,EAAGE,OAAOM,WAAW6E,EAAGD,YACxBpF,GAAGE,OAAOO,MAAM4E,EAAGD,UAAY,CAC/BpF,GAAGE,OAAOO,MAAMC,kBAGjB,IAAIV,EAAGE,OAAOM,WAAW6E,EAAGD,UAAUC,EAAGJ,IACzC,CACCjF,EAAGE,OAAOM,WAAW6E,EAAGD,UAAUC,EAAGJ,IAAMjF,EAAGsF,MAAMD,EACpDrF,GAAGE,OAAOO,MAAM4E,EAAGD,WAEnBpF,GAAG,yBAAyBkF,MAAMC,QAAU,MAC5CnF,GAAG,4BAA4BqF,EAAGD,UAAUF,MAAMC,QAAU,EAC5DnF,GAAG,yBAAyBqF,EAAGD,UAAUG,YAAYvF,EAAGsB,OAAO,OAC9DkD,OACClC,UAAY,8BACZ2C,GAAM,wBAAwBI,EAAGJ,IAElCO,KAAM,sEAAsEH,EAAGD,SAAS,OAASC,EAAGJ,GAAG,oGAAoGI,EAAGI,KAAK,YAGpNzF,GAAG,oBAAoBqF,EAAGD,UAAUM,UAAY,IAAI1F,EAAGE,OAAOO,MAAM4E,EAAGD,UAAU,KAInFpF,GAAGE,OAAOyF,gBAAkB,SAASP,EAAUH,SAEvCjF,GAAGE,OAAOM,WAAW4E,GAAUH,EAEtCjF,GAAGE,OAAOO,MAAM2E,IAEhB,IAAIQ,GAAO5F,EAAG,wBAAwBiF,EACtCW,GAAKC,WAAWC,YAAYF,EAE5B,IAAG5F,EAAGE,OAAOO,MAAM2E,IAAa,EAChC,OACQpF,GAAGE,OAAOM,WAAW4E,EAC5BpF,GAAGE,OAAOO,MAAMC,iBAEhBV,GAAG,4BAA4BoF,GAAUF,MAAMC,QAAU,MAEzD,IAAGnF,EAAGE,OAAOO,MAAMC,iBAAmB,EACrCV,EAAG,yBAAyBkF,MAAMC,QAAU,OAG9C,CACCnF,EAAG,oBAAoBoF,GAAUM,UAAY,IAAI1F,EAAGE,OAAOO,MAAM2E,GAAU,IAG5EpF,EAAG4D,cAAc5D,EAAGE,OAAQ,iBAAkBkF,SAAYA,EAAUH,GAAMA,KAG3EjF,GAAGE,OAAO4D,eAAiB,WAE1B,IAAI,GAAIsB,KAAYpF,GAAGE,OAAOM,WAC7B,IAAI,GAAIyE,KAAMjF,GAAGE,OAAOM,WAAW4E,GAClCpF,EAAGE,OAAOyF,gBAAgBP,EAAUH,EACtCjF,GAAGE,OAAOM,cAGXR,GAAGE,OAAOuC,QAAU,WAEnBzC,EAAGmD,KAAKC,KAAK,mCACZ2C,IAAK/F,EAAGE,OAAOM,WACfwF,KAAM,WACNC,OAAQjG,EAAGkG,kBAIblG,GAAGE,OAAOwC,aAAe,WAExB,GAAG1C,EAAGE,OAAOW,eAAiBb,EAAGE,OAAOU,eAAeZ,EAAGE,OAAO8D,MAChEhE,EAAGE,OAAOU,eAAeZ,EAAGE,OAAO8D,QAEpC,KAAI,GAAImC,KAAMnG,GAAGE,OAAOM,WACxB,CACC,IAAI,GAAIyE,KAAMjF,GAAGE,OAAOM,WAAW2F,GACnC,CACC,GAAInG,EAAGE,OAAOW,aACbb,EAAGE,OAAOU,eAAeZ,EAAGE,OAAO8D,MAAMiB,IAAOA,GAAIA,EAAIG,SAAUe,EAAIV,KAAMzF,EAAGE,OAAOM,WAAW2F,GAAIlB,GAAIQ,UAEzGzF,GAAGE,OAAOU,eAAeZ,EAAGE,OAAO8D,MAAMiB,GAAM,OAKnDjF,GAAGE,OAAOkG,YAAc,SAASC,EAAOrC,GAEvC,IAAIA,EACHA,EAAO,MAERhE,GAAGE,OAAOU,eAAeoD,GAAQqC,EAGlCrG,GAAGE,OAAOoG,eAAiB,SAASrB,EAAIjB,GAEvC,IAAIA,EACHA,EAAO,MAER,IAAGhE,EAAGE,OAAOU,eAAeoD,IAAShE,EAAGE,OAAOU,eAAeoD,GAAMiB,GACpE,OACQjF,GAAGE,OAAOU,eAAeoD,GAAMiB,IAIxCjF,GAAGE,OAAOqG,gBAAkB,SAASnB,GAEpC,GAAGpF,EAAGE,OAAOc,gBAAgBoE,GAC5B,MAAOpF,GAAGE,OAAOc,gBAAgBoE,GAAUK,IAC5C,OAAO,GAGRzF,GAAGE,OAAOsG,kBAAoB,SAASpB,EAAUH,GAEhD,GAAGjF,EAAGE,OAAOc,gBAAgBoE,GAC7B,CACC,GAAIqB,GAAWzG,EAAGE,OAAOc,gBAAgBoE,GAAU,WACnD,KAAI,GAAIsB,KAAKD,GACb,CACC,GAAIE,GAAO,GAAIC,QAAOH,EAASC,GAAG,WAClC,IAAGC,EAAKE,KAAK5B,GACb,CACC,MAAOwB,GAASC,GAAG,WAGrB,MAAO1G,GAAGE,OAAOc,gBAAgBoE,GAAUK,KAE5C,MAAO"}