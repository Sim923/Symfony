{"version":3,"file":"logic.min.js","sources":["logic.js"],"names":["BX","namespace","Tasks","Component","TaskViewSidebar","parameters","this","layout","stagesWrap","stages","taskId","messages","workingTime","start","hours","minutes","end","can","allowTimeTracking","user","isAmAuditor","iAmAuditor","auditorCtrl","pathToTasks","stageId","parseInt","query","Util","Query","initDeadline","initReminder","initMark","initTime","initTags","initAuditorThing","initStages","addCustomEvent","window","delegate","onTaskEvent","onChangeProjectLink","prototype","EDIT","Dispatcher","find","then","ctrl","bindControl","onToggleImAuditor","bind","canChange","SORT","stagesShowed","length","cleanNode","i","c","appendChild","TEXT_LAYOUT","create","attrs","data-stageId","ID","title","TITLE","props","className","text","events","click","setStageHadnler","style","cursor","show","setStage","hide","groupId","data","entityId","entityType","run","result","isSuccess","numeric","execute","getStageData","id","proxy_context","saveStage","fireGlobalTaskEvent","STAGE_ID","STAY_AT_PAGE","stage","color","clearAll","calculateTextColor","backgroundColor","borderBottomColor","COLOR","baseColor","defaultColors","r","g","b","util","in_array","toLowerCase","split","join","y","confirm","message","syncAuditor","deleteItem","addItem","setHeaderButtonLabelText","getData","READ","document","location","reload","type","task","isNotEmptyString","REAL_STATUS","STATUS_CHANGED_DATE","setStatus","status","time","statusName","statusDate","innerHTML","htmlspecialchars","deadline","deadlineClear","proxy","onDeadlineClick","clearDeadline","event","now","Date","today","UTC","getFullYear","getMonth","getDate","calendar","node","field","form","bTime","value","bHideTimebar","callback_after","setDeadline","ValueToString","date","format","convertThurlyFormat","replace","display","updateDeadline","emptyDeadline","add","DEADLINE","onCustomEvent","addReminder","reminderAdd","mark","onMarkClick","TaskGradePopup","listValue","onPopupChange","onMarkChange","popup","listItem","name","MARK","bindEvent","onTaskTimerTick","formatTimeAmount","saveTags","tags","tagsString","TAGS","call"],"mappings":"AAAAA,GAAGC,UAAU,oBAEb,WAEC,SAAUD,IAAGE,MAAMC,UAAUC,iBAAmB,YAChD,CACC,OAGDJ,GAAGE,MAAMC,UAAUC,gBAAkB,SAASC,GAE7CC,KAAKC,QACJC,WAAYR,GAAG,mBACfS,OAAQT,GAAG,eAEZM,MAAKD,WAAaA,KAClBC,MAAKI,OAASJ,KAAKD,WAAWK,MAC9BJ,MAAKK,SAAWL,KAAKD,WAAWM,YAChCL,MAAKM,YAAcN,KAAKD,WAAWO,cAAiBC,OAAUC,MAAO,EAAGC,QAAS,GAAKC,KAAQF,MAAO,EAAGC,QAAS,GACjHT,MAAKW,IAAMX,KAAKD,WAAWY,OAC3BX,MAAKY,kBAAoBZ,KAAKD,WAAWa,oBAAsB,IAC/DZ,MAAKa,KAAOb,KAAKD,WAAWc,QAC5Bb,MAAKc,YAAcd,KAAKD,WAAWgB,UACnCf,MAAKgB,YAAc,IACnBhB,MAAKiB,YAAcjB,KAAKD,WAAWkB,WACnCjB,MAAKkB,QAAUC,SAASnB,KAAKD,WAAWmB,QACxClB,MAAKG,OAASH,KAAKD,WAAWI,UAC9BH,MAAKoB,MAAQ,GAAI1B,IAAGE,MAAMyB,KAAKC,KAE/BtB,MAAKuB,cACLvB,MAAKwB,cACLxB,MAAKyB,UACLzB,MAAK0B,UACL1B,MAAK2B,UACL3B,MAAK4B,kBACL5B,MAAK6B,YAELnC,IAAGoC,eAAeC,OAAQ,iBAAkBrC,GAAGsC,SAAShC,KAAKiC,YAAajC,MAC1EN,IAAGoC,eAAeC,OAAQ,sBAAuBrC,GAAGsC,SAAShC,KAAKkC,oBAAqBlC,OAGzFN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUP,iBAAmB,WAE/D,IAAI5B,KAAKW,IAAIyB,KACb,CACC1C,GAAGE,MAAMyB,KAAKgB,WAAWC,KAAK,oBAAoBC,KAAK,SAASC,GAC/DxC,KAAKgB,YAAcwB,CACnBA,GAAKC,YAAY,gBAAiB,QAASzC,KAAK0C,kBAAkBC,KAAK3C,QACtE2C,KAAK3C,QAQTN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUN,WAAa,WAEzD,GAAI7B,KAAKC,OAAOE,QAAUH,KAAKG,OAC/B,CACC,GAAIyC,GAAY5C,KAAKD,WAAWY,IAAIkC,IACpC,IAAIC,GAAe9C,KAAKG,OAAO4C,OAAS,CAExCrD,IAAGsD,UAAUhD,KAAKC,OAAOE,OAEzB,KAAK,GAAI8C,GAAE,EAAGC,EAAElD,KAAKG,OAAO4C,OAAQE,EAAEC,EAAGD,IACzC,CACCjD,KAAKC,OAAOE,OAAOgD,YAClBnD,KAAKG,OAAO8C,GAAGG,YAAc1D,GAAG2D,OAAO,OACtCC,OACCC,eAAgBvD,KAAKG,OAAO8C,GAAGO,GAC/BC,MAAOzD,KAAKG,OAAO8C,GAAGS,OAEvBC,OACCC,UAAW,4BAEZC,KAAM7D,KAAKG,OAAO8C,GAAGS,MACrBI,OACClB,GAECmB,MAAOrE,GAAGsC,SAAShC,KAAKgE,gBAAiBhE,OAEvC,KACJiE,OACErB,GAEAsB,OAAQ,WAEN,QAKP,GAAIpB,EACJ,CACCpD,GAAGyE,KAAKnE,KAAKC,OAAOC,WAEpB,IAAIF,KAAKkB,QAAU,EACnB,CACClB,KAAKoE,SAASpE,KAAKkB,aAGpB,CACClB,KAAKoE,SAASpE,KAAKG,OAAO,GAAGqD,SAI/B,CACC9D,GAAG2E,KAAKrE,KAAKC,OAAOC,cAWvBR,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUD,oBAAsB,SAASoC,EAASlE,GAEpFkE,EAAUnD,SAASmD,EAGnBtE,MAAKkB,QAAU,CAGf,IAAIoD,IAAY,EAChB,CACCtE,KAAKG,SACLH,MAAK6B,iBAGN,CACC,GAAI0C,IACHC,SAAUF,EACVG,WAAY,IAEbzE,MAAKoB,MAAMsD,IAAI,0BAA2BH,GAAMhC,KAAK,SAASoC,GAC7D,GAAIA,EAAOC,YACX,CACC5E,KAAKD,WAAWY,IAAIkC,KAAO8B,EAAOJ,OAAS,OAE3C5B,KAAK3C,MAEP,IAAIuE,IACHC,SAAUF,EACVO,QAAS,KAEV7E,MAAKoB,MAAMsD,IAAI,kBAAmBH,GAAMhC,KAAK,SAASoC,GACrD,GAAIA,EAAOC,YACX,CACC5E,KAAKG,OAASwE,EAAOJ,IACrBvE,MAAK6B,eAELc,KAAK3C,MAEPA,MAAKoB,MAAM0D,WASbpF,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAU4C,aAAe,SAAS7D,GAEpEA,EAAUC,SAASD,EAEnB,IAAIlB,KAAKG,OACT,CACC,IAAK,GAAI6E,KAAMhF,MAAKG,OACpB,CACC,GAAIgB,SAASnB,KAAKG,OAAO6E,GAAIxB,MAAQtC,EACrC,CACC,MAAOlB,MAAKG,OAAO6E,KAKtB,MAAO,MAORtF,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAU6B,gBAAkB,WAE9D,GAAI9C,GAAUxB,GAAG6E,KAAK7E,GAAGuF,cAAe,UACxCjF,MAAKoE,SAASlD,EACdlB,MAAKkF,UAAUhE,GAQhBxB,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAU+C,UAAY,SAAShE,GAEjEA,EAAUC,SAASD,EACnB,IAAIA,IAAYlB,KAAKkB,QACrB,CACC,OAEDlB,KAAKkB,QAAUA,CACf,IAAIqD,IACHS,GAAIhF,KAAKI,OACTc,QAASA,EAEVlB,MAAKoB,MAAMsD,IAAI,uBAAwBH,GAAMhC,KAAK,SAASoC,GAC1D,GAAIA,EAAOC,YACX,CACClF,GAAGE,MAAMyB,KAAK8D,oBACb,gBACC3B,GAAIe,EAAKS,GAAII,SAAUb,EAAKrD,UAC5BmE,aAAc,OACdL,GAAIT,EAAKS,OAGXrC,KAAK3C,MACPA,MAAKoB,MAAM0D,UAQZpF,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUiC,SAAW,SAASlD,GAEhE,GAAIoE,GAAQtF,KAAK+E,aAAa7D,EAC9BA,GAAUC,SAASD,EAEnB,IAAIlB,KAAKG,QAAUmF,EACnB,CACC,GAAIC,GAAQ,IAAMD,EAAM,QACxB,IAAIE,GAAW,IACf,IAAIvF,EACJ,KAAK,GAAIgD,GAAE,EAAGC,EAAElD,KAAKG,OAAO4C,OAAQE,EAAEC,EAAGD,IACzC,CACChD,EAASD,KAAKG,OAAO8C,GAAGG,WACxB,IAAIoC,EACJ,CACCvF,EAAOgE,MAAMsB,MAAQvF,KAAKyF,mBAAmBF,EAC7CtF,GAAOgE,MAAMyB,gBAAkBH,CAC/BtF,GAAOgE,MAAM0B,kBAAoBJ,MAGlC,CACCtF,EAAOgE,MAAMyB,gBAAkB,EAC/BzF,GAAOgE,MAAM0B,kBAAoB,IAAM3F,KAAKG,OAAO8C,GAAG2C,MAEvD,GAAIzE,SAASnB,KAAKG,OAAO8C,GAAGO,MAAQtC,EACpC,CACCsE,EAAW,SAWf9F,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUsD,mBAAqB,SAASI,GAE1E,GAAIC,IACH,SACA,SACA,SACA,SACA,SACA,SACA,SAED,IAAIC,GAAGC,EAAGC,CAEV,IAAIvG,GAAGwG,KAAKC,SAASN,EAAUO,cAAeN,GAC9C,CACC,MAAO,WAGR,CACC,GAAI5C,GAAI2C,EAAUQ,MAAM,GACxB,IAAInD,EAAEH,QAAS,EAAE,CAChBG,GAAIA,EAAE,GAAIA,EAAE,GAAIA,EAAE,GAAIA,EAAE,GAAIA,EAAE,GAAIA,EAAE,IAErCA,EAAI,KAAOA,EAAEoD,KAAK,GAClBP,GAAM7C,GAAK,GAAO,GAClB8C,GAAM9C,GAAK,EAAM,GACjB+C,GAAK/C,EAAI,IAGV,GAAIqD,GAAI,IAAOR,EAAI,IAAOC,EAAI,IAAOC,CACrC,OAASM,GAAI,IAAQ,OAAS,OAG/B7G,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUO,kBAAoB,WAEhE,GAAG1C,KAAKc,YACR,CACCpB,GAAGE,MAAM4G,QAAQ9G,GAAG+G,QAAQ,wDAAwDlE,KAAK,WACxFvC,KAAK0G,eACJ/D,KAAK3C,WAGR,CACCA,KAAK0G,eAIPhH,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUuE,YAAc,WAE1D,GAAI1B,GAAKhF,KAAKI,MACd,IAAIgB,GAAQ,GAAI1B,IAAGE,MAAMyB,KAAKC,KAG9BF,GAAMsD,IAAI,SAAS1E,KAAKc,YAAc,eAAiB,iBAAkBkE,GAAIA,IAAKzC,KAAK,SAASoC,GAE/F,GAAGA,EAAOC,YACV,CACC5E,KAAKa,KAAK4D,WAAa,GAGvB,IAAGzE,KAAKc,YACR,CACCd,KAAKgB,YAAY2F,WAAW3G,KAAKa,UAGlC,CACCb,KAAKgB,YAAY4F,QAAQ5G,KAAKa,MAG/Bb,KAAKc,aAAed,KAAKc,WACzBd,MAAKgB,YAAY6F,yBAChB7G,KAAKc,YACLpB,GAAG+G,QAAQ,+CACX/G,GAAG+G,QAAQ,kDAIZ9D,KAAK3C,MAGPoB,GAAMsD,IAAI,qBAAsBM,GAAIA,IAAKzC,KAAK,SAASoC,GACtD,GAAGA,EAAOC,YACV,CACC,GAAIL,GAAOI,EAAOmC,SAElB,KAAIvC,EAAKwC,KACT,CACC,GAAG/G,KAAKiB,YACR,CACCc,OAAOiF,SAASC,SAAWjH,KAAKiB,gBAGjC,CACCvB,GAAGwH,aAILvE,KAAK3C,MAEPoB,GAAM0D,UAGNpF,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUF,YAAc,SAASkF,EAAMpH,GAEzEA,EAAaA,KACb,IAAIwE,GAAOxE,EAAWqH,QAEtB,IAAGD,GAAQ,UAAY5C,EAAKf,IAAMxD,KAAKD,WAAWK,OAClD,CAGC,GAAGV,GAAGyH,KAAKE,iBAAiB9C,EAAK+C,cAAgB5H,GAAGyH,KAAKE,iBAAiB9C,EAAKgD,qBAC/E,CACCvH,KAAKwH,UAAUjD,EAAK+C,YAAa/C,EAAKgD,uBAKzC7H,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUqF,UAAY,SAASC,EAAQC,GAEzE,GAAIC,GAAajI,GAAG,0BACpB,IAAIkI,GAAalI,GAAG,0BAEpBiI,GAAWE,UAAYnI,GAAG+G,QAAQ,gBAAkBgB,EACpDG,GAAWC,WAAaJ,GAAU,GAAKA,GAAU,EAChD/H,GAAG+G,QAAQ,4BAA8B,IAAM,IAC/C/G,GAAGwG,KAAK4B,iBAAiBJ,GAG3BhI,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUZ,aAAe,WAE3DvB,KAAK+H,SAAWrI,GAAGyH,KAAKE,iBAAiBrH,KAAKD,WAAWgI,UAAY/H,KAAKD,WAAWgI,SAAW,EAChG/H,MAAKC,OAAO8H,SAAWrI,GAAG,uBAC1BM,MAAKC,OAAO+H,cAAgBtI,GAAG,6BAE/B,KAAKM,KAAKC,OAAO8H,SACjB,CACC,OAGDrI,GAAGiD,KAAK3C,KAAKC,OAAO8H,SAAU,QAASrI,GAAGuI,MAAMjI,KAAKkI,gBAAiBlI,MACtEN,IAAGiD,KAAK3C,KAAKC,OAAO+H,cAAe,QAAStI,GAAGuI,MAAMjI,KAAKmI,cAAenI,OAG1EN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAU+F,gBAAkB,SAASE,GAEvE,GAAIC,GAAM,GAAIC,KACd,IAAIC,GAAQ,GAAID,MAAKA,KAAKE,IACzBH,EAAII,cACJJ,EAAIK,WACJL,EAAIM,UACJ3I,KAAKM,YAAYI,IAAIF,MACrBR,KAAKM,YAAYI,IAAID,SAGtBf,IAAGkJ,UACFC,KAAM7I,KAAKC,OAAO8H,SAClBe,MAAO,GACPC,KAAM,GACNC,MAAO,KACPC,MAAOjJ,KAAK+H,SAAW/H,KAAK+H,SAAWQ,EACvCW,aAAc,MACdC,eAAgBzJ,GAAGuI,MAAM,SAASgB,EAAOvB,GACxC1H,KAAKoJ,YAAYH,IACfjJ,QAILN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUiH,YAAc,SAASrB,GAEnE/H,KAAK+H,SAAWrI,GAAGkJ,SAASS,cAActB,EAAU,KAAM,MAE1D/H,MAAKC,OAAO8H,SAASF,UAAYnI,GAAG4J,KAAKC,OACxC7J,GAAG4J,KAAKE,oBACP9J,GAAG+G,QAAQ,mBAAmBgD,QAAQ,MAAO,IAAIA,QAAQ,MAAO,KACjE1B,EACA,KACA,MAED/H,MAAKC,OAAO+H,cAAc/D,MAAMyF,QAAU,EAE1C1J,MAAK2J,iBAGNjK,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUgG,cAAgB,WAE5DnI,KAAK+H,SAAW,EAChB/H,MAAKC,OAAO8H,SAASF,UAAY7H,KAAKK,SAASuJ,aAC/C5J,MAAKC,OAAO+H,cAAc/D,MAAMyF,QAAU,MAE1C1J,MAAK2J,iBAGNjK,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUwH,eAAiB,WAE7D,GAAIvI,GAAQ,GAAI1B,IAAGE,MAAMyB,KAAKC,KAC9BF,GAAMyI,IAAI,eAAiB7E,GAAIhF,KAAKI,OAAQmE,MAAQuF,SAAU9J,KAAK+H,cAAkBrI,GAAGsC,SAAS,WAChGtC,GAAGqK,cAAchI,OAAQ,gCAAiC/B,KAAKI,OAAQJ,KAAK+H,UAG5ErI,IAAGE,MAAMyB,KAAK8D,oBAAoB,UAAW3B,GAAIxD,KAAKI,SAAUiF,aAAc,OAAQL,GAAIhF,KAAKI,OAAQ2H,SAAU/H,KAAK+H,YAEpH/H,MACHoB,GAAM0D,UAGPpF,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAU6H,YAAc,WAE1DtK,GAAGqK,cAAchI,OAAQ,6BAA8B/B,KAAKC,OAAOgK,cAGpEvK,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUX,aAAe,WAE3DxB,KAAKC,OAAOgK,YAAcvK,GAAG,2BAC7BA,IAAGiD,KAAK3C,KAAKC,OAAOgK,YAAa,QAASvK,GAAGsC,SAAShC,KAAKgK,YAAahK,OAGzEN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUV,SAAW,WAEvD,IAAKzB,KAAKW,IAAI,QACd,CACC,OAGDX,KAAKkK,KAAOlK,KAAKD,WAAWmK,MAAQ,MACpClK,MAAKC,OAAOiK,KAAOxK,GAAG,mBACtB,IAAIM,KAAKC,OAAOiK,KAChB,CACCxK,GAAGiD,KAAK3C,KAAKC,OAAOiK,KAAM,QAASxK,GAAGuI,MAAMjI,KAAKmK,YAAanK,QAIhEN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUgI,YAAc,WAE1DzK,GAAG0K,eAAejG,KACjBnE,KAAKI,OACLJ,KAAKC,OAAOiK,MAEXG,UAAWrK,KAAKkK,OAGhBpG,QACCwG,cAAgB5K,GAAGuI,MAAMjI,KAAKuK,aAAcvK,SAMhDN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUoI,aAAe,WAE3D,GAAIC,GAAQ9K,GAAGuF,aAEfjF,MAAKC,OAAOiK,KAAKtG,UAAY,iCAAmC4G,EAAMH,UAAUjE,aAChFpG,MAAKC,OAAOiK,KAAKrC,UAAY2C,EAAMC,SAASC,IAE5C,IAAItJ,GAAQ,GAAI1B,IAAGE,MAAMyB,KAAKC,KAC9BF,GAAMyI,IAAI,eAAiB7E,GAAIhF,KAAKI,OAAQmE,MAAQoG,KAAMH,EAAMH,YAAc,OAAS,GAAMG,EAAMH,YACnG,IAAIjK,GAASJ,KAAKI,MAClBgB,GAAM0D,UAAUvC,KAAK,WACpB7C,GAAGE,MAAMyB,KAAK8D,oBAAoB,UAAW3B,GAAIpD,IAAUiF,aAAc,OAAQL,GAAI5E,MAIvFV,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUT,SAAW,WAEvD,IAAK1B,KAAKY,kBACV,CACC,OAGDlB,GAAGE,MAAMyB,KAAKgB,WAAWuI,UAAU,kBAAmB,kBAAmBlL,GAAGsC,SAAShC,KAAK6K,gBAAiB7K,OAG5GN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAU0I,gBAAkB,SAASzK,EAAQsH,GAE/E,GAAItH,GAAUJ,KAAKI,OACnB,CACC,OAGD,GAAIyI,GAAOnJ,GAAG,0BAA4BM,KAAKI,OAC/C,IAAIyI,EACJ,CACCA,EAAKhB,UAAYnI,GAAGE,MAAMyB,KAAKyJ,iBAAiBpD,IAIlDhI,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAUR,SAAW,WAEvDjC,GAAGoC,eAAe,kBAAmBpC,GAAGuI,MAAMjI,KAAK+K,SAAU/K,OAG9DN,IAAGE,MAAMC,UAAUC,gBAAgBqC,UAAU4I,SAAW,SAASC,GAEhE,GAAIC,GAAa,EACjB,KAAK,GAAIhI,GAAI,EAAGF,EAASiI,EAAKjI,OAAQE,EAAIF,EAAQE,IAClD,CACC,GAAIA,EAAI,EACR,CACCgI,GAAc,KAGfA,GAAcD,EAAK/H,GAAGyH,KAGvB,GAAItJ,GAAQ,GAAI1B,IAAGE,MAAMyB,KAAKC,KAC9BF,GAAMyI,IAAI,eAAiB7E,GAAIhF,KAAKI,OAAQmE,MAAQ2G,KAAMD,IAC1D7J,GAAM0D,aAGLqG,KAAKnL"}