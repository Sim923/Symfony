{"version":3,"file":"dayplan.min.js","sources":["dayplan.js"],"names":["BX","namespace","Tasks","DayPlan","Util","Base","extend","options","query","data","methods","construct","this","callConstruct","vars","state","timeOffsets","taskData","clone","option","onPlannerUpdate","debounce","bindWidgetEvents","window","top","ready","delegate","hasPlanner","setInterval","timerTickEmulation","addCustomEvent","onTaskGlobalEvent","windowObj","PLANNER","obPlanner","onTimerUpdate","TASKS","TASK_ON_TIMER","taskId","k","planCame","length","parseInt","ID","isNaN","timer","plan","fireEvent","updatePlanner","updated","BXTIMEMAN","Update","BXPLANNER","update","action","inLog","TASK","TIME_SPENT_IN_LOGS","inTimer","TIMER","RUN_TIME","TIMER_IS_RUNNING_FOR_CURRENT_USER","type","task","setTaskTimeOffset","TIME_ELAPSED","value","current","tasks","time","addToPlan","addTaskToPlanner","startTimer","sync","stopPrevious","getQuery","add","errors","error","getByCode","args","TITLE","id","title","deleteByCodeAll","stopTimer","Query","autoExec"],"mappings":"AAAAA,GAAGC,UAAU,WAebD,IAAGE,MAAMC,QAAUH,GAAGE,MAAME,KAAKC,KAAKC,QACrCC,SACCC,MAAO,MACPC,SAEDC,SAECC,UAAW,WAEVC,KAAKC,cAAcb,GAAGE,MAAME,KAAKC,KAEjC,UAAUO,MAAKE,MAAQ,YACvB,CACCF,KAAKE,QAENF,KAAKE,KAAKC,QACVH,MAAKE,KAAKE,cACVJ,MAAKE,KAAKG,SAAWjB,GAAGkB,MAAMN,KAAKO,OAAO,QAE1CP,MAAKQ,gBAAkBpB,GAAGqB,SAAST,KAAKQ,gBAAiB,IAAKR,KAE9DA,MAAKU,iBAAiBC,QAAUA,OAAOC,IAAMD,OAAOC,IAAMD,OAE1DvB,IAAGyB,MAAMzB,GAAG0B,SAAS,WACpB,IAAKd,KAAKe,aACV,CAECC,YAAY5B,GAAG0B,SAASd,KAAKiB,mBAAoBjB,MAAO,IAGxDZ,IAAG8B,eAAeP,OAAQ,iBAAkBvB,GAAG0B,SAASd,KAAKmB,kBAAmBnB,SAE/EA,QAGJU,iBAAkB,SAASU,GAE1BA,EAAUhC,GAAG8B,eACZE,EACA,wBACAhC,GAAG0B,SAAS,SAASjB,GACpBG,KAAKQ,gBAAgBX,EAAKwB,UACxBrB,MAEJoB,GAAUhC,GAAG8B,eACZE,EACA,wBACAhC,GAAG0B,SAAS,SAASQ,EAAWzB,GAC/BG,KAAKQ,gBAAgBX,IACnBG,MAGJoB,GAAUhC,GAAG8B,eACZE,EACA,oBACAhC,GAAG0B,SAAS,SAASjB,GACpBG,KAAKuB,cAAc1B,IACjBG,QAKLQ,gBAAiB,SAASX,GAEzBA,EAAOA,KACPA,GAAK2B,MAAQ3B,EAAK2B,SAClB3B,GAAK4B,cAAgB5B,EAAK4B,iBAE1B,IAAIC,EACJ,IAAIC,EACJ,IAAIC,KAEJ,KAAID,EAAI,EAAGA,EAAI9B,EAAK2B,MAAMK,OAAQF,IAClC,CACCD,EAASI,SAASjC,EAAK2B,MAAMG,GAAGI,GAChC,IAAGC,MAAMN,GACT,CACC,SAEDE,EAASF,GAAU,IAEnB,UAAU1B,MAAKE,KAAKC,MAAMuB,IAAW,YACrC,CACC1B,KAAKE,KAAKC,MAAMuB,IAAWO,MAAO,MAAOC,KAAM,OAEhDlC,KAAKE,KAAKC,MAAMuB,GAAQQ,KAAO,KAGhC,IAAIP,IAAK3B,MAAKE,KAAKC,MACnB,CACC,SAAUyB,GAASD,IAAM,YACzB,CACC3B,KAAKmC,UAAU,oBAAqBR,EAAG,OACvC3B,MAAKE,KAAKC,MAAMwB,GAAGO,KAAO,OAI5BR,EAASI,SAASjC,EAAK4B,cAAcM,GAErC,KAAIC,MAAMN,GACV,CACC,SAAU1B,MAAKE,KAAKC,MAAMuB,IAAW,YACrC,CACC1B,KAAKE,KAAKC,MAAMuB,IAAWO,MAAO,MAAOC,KAAM,OAGhD,IAAIP,IAAK3B,MAAKE,KAAKC,MACnB,CACC,GAAGH,KAAKE,KAAKC,MAAMwB,GAAGM,OAASN,GAAKD,EACpC,CACC1B,KAAKmC,UAAU,qBAAsB,MAAOR,IAG7C3B,KAAKE,KAAKC,MAAMwB,GAAGM,MAAQ,MAE5BjC,KAAKE,KAAKC,MAAMuB,GAAQO,MAAQ,OAKlCG,cAAe,WAEd,IAAIpC,KAAKe,aACT,CACC,MAAO,OAGR,GAAIsB,GAAU,IAId,IAAI1B,OAAO2B,UACV3B,OAAO2B,UAAUC,OAAO,UACpB,IAAI5B,OAAO6B,WAAa7B,OAAO6B,UAAUC,OAC7C9B,OAAO6B,UAAUC,aAEjBJ,GAAU,KAEX,IAAI1B,OAAOC,MAAQD,OACnB,CACC,GAAIA,OAAOC,IAAI0B,UACd3B,OAAOC,IAAI0B,UAAUC,OAAO,UACxB,IAAI5B,OAAOC,IAAI4B,WAAa7B,OAAOC,IAAI4B,UAAUC,OACrD9B,OAAOC,IAAI4B,UAAUC,SAGvB,MAAO,IAGR1B,WAAY,WAEX,SAAUJ,OAAOC,IAAI4B,WAAa7B,OAAOC,IAAI0B,YAI9Cf,cAAe,SAAS1B,GAEvBA,EAAOA,KACPA,GAAKQ,SAAWR,EAAKQ,YAErB,IAAGR,EAAK6C,QAAU,uBAClB,CACC,GAAIC,GAAQb,SAASjC,EAAKA,KAAK+C,KAAKC,mBACpC,IAAGb,MAAMW,GACT,CACCA,EAAQ,EAET,GAAIG,GAAUhB,SAASjC,EAAKA,KAAKkD,MAAMC,SACvC,IAAGhB,MAAMc,GACT,CACCA,EAAU,EAGX9C,KAAKmC,UAAU,mBAAoBtC,EAAK6B,OAAQiB,EAAQG,EAASjD,EAAKA,KAAK+C,WAEvE,IAAG/C,EAAK6C,QAAU,aACvB,CACC7C,EAAKQ,SAAS4C,kCAAoC,KAClDjD,MAAKmC,UAAU,qBAAsBtC,EAAK6B,OAAQ,MAAO7B,EAAKQ,eAE1D,IAAGR,EAAK6C,QAAU,cACvB,CACC7C,EAAKQ,SAAS4C,kCAAoC,IAClDjD,MAAKmC,UAAU,qBAAsBtC,EAAK6B,OAAQ,KAAM7B,EAAKQ,aAI/Dc,kBAAmB,SAAS+B,EAAMrD,GAEjC,GAAGqD,GAAQ,gBAAmBrD,GAAKsD,MAAQ,aAAetD,EAAKsD,KAAKpB,GACpE,CACC/B,KAAKE,KAAKG,SAASR,EAAKsD,KAAKpB,IAAM3C,GAAGkB,MAAMT,EAAKsD,KACjDnD,MAAKoD,kBAAkBvD,EAAKsD,KAAKpB,GAAIlC,EAAKsD,KAAKE,gBAIjDD,kBAAmB,SAAS1B,EAAQ4B,GAEnCtD,KAAKE,KAAKE,YAAYsB,IAAWiB,MAAOb,SAASwB,GAAQC,QAAS,IAInEtC,mBAAoB,WAEnB,GAAIuC,GAAQxD,KAAKE,KAAKG,QAEtB,KAAI,GAAIsB,KAAK6B,GACb,CACC,GAAI3D,GAAO2D,EAAM7B,EAEjB,IAAG9B,EAAKkC,IAAMlC,EAAKoD,kCACnB,CACC,SAAUjD,MAAKE,KAAKE,YAAYP,EAAKkC,KAAO,YAC5C,CACC/B,KAAKoD,kBAAkBvD,EAAKkC,GAAIlC,EAAKwD,cAEtC,GAAII,GAAOzD,KAAKE,KAAKE,YAAYP,EAAKkC,GAEtC/B,MAAKmC,UAAU,mBAAoBtC,EAAKkC,GAAI0B,EAAKd,MAAQc,EAAKF,QAAS1D,GACvE4D,GAAKF,aAKRG,UAAW,SAAShC,GAEnB,GAAGtC,GAAGuE,iBACN,CACCvE,GAAGuE,iBAAiBjC,EACpB,OAAO,UAEH,IAAGf,OAAOC,IAAIxB,GAAGuE,iBACtB,CACChD,OAAOC,IAAIxB,GAAGuE,iBAAiBjC,EAC/B,OAAO,MAER,MAAO,QAERkC,WAAY,SAASlC,EAAQmC,EAAMC,GAElC,IAAIpC,EACJ,CACC,OAGD,GAAGmC,EACH,CACC7D,KAAK+D,WAAWC,IAAI,4BAA6BtC,OAAQA,EAAQoC,aAAcA,GAAgB,UAAY1E,GAAG0B,SAAS,SAASmD,GAE/H,GAAIC,GAAQD,EAAOE,UAAU,sBAE7B,IAAGD,EACH,CACC,GAAIrE,GAAOqE,EAAMrE,MACjB,IAAIuE,IAAQ1C,KACZ,IAAG7B,EAAK+C,MAAQ/C,EAAK+C,KAAKyB,OAASxE,EAAK+C,KAAKb,GAC7C,CACCqC,EAAK,IAAME,GAAIzE,EAAK+C,KAAKb,GAAIwC,MAAO1E,EAAK+C,KAAKyB,OAG/CrE,KAAKmC,UAAU,sBAAuBiC,EAEtCH,GAAOO,gBAAgB,2BAGxB,CACCxE,KAAKmC,UAAU,qBAAsBT,EAAQ,SAE5C1B,WAGJ,CACCA,KAAKmC,UAAU,qBAAsBT,EAAQ,SAG/C+C,UAAW,SAAS/C,EAAQmC,GAE3B,IAAInC,EACJ,CACC,OAGD,GAAGmC,EACH,CACC7D,KAAK+D,WAAWC,IAAI,2BAA4BtC,OAAQA,MAAatC,GAAG0B,SAAS,WAChFd,KAAKmC,UAAU,qBAAsBT,EAAQ,SAC3C1B,WAGJ,CACCA,KAAKmC,UAAU,qBAAsBT,EAAQ,UAI/CqC,SAAU,WAET,SAAU/D,MAAKJ,OAAS,YACxB,CACC,GAAGI,KAAKO,OAAO,SACf,CACCP,KAAKJ,MAAQI,KAAKO,OAAO,aAG1B,CACCP,KAAKJ,MAAQ,GAAIR,IAAGE,MAAME,KAAKkF,OAC9BC,SAAU,QAKb,MAAO3E,MAAKJ"}