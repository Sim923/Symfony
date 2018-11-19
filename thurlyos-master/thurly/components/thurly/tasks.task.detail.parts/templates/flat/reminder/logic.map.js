{"version":3,"file":"logic.min.js","sources":["logic.js"],"names":["BX","namespace","Tasks","Component","TaskDetailPartsReminder","Util","ItemSet","extend","sys","code","constants","UNIT_DAY","UNIT_HOUR","methods","construct","this","callConstruct","vars","editItemValue","instances","windows","form","Form","scope","control","id","parent","setTaskId","option","setTaskDeadLine","bindEvent","delegate","onItemApply","addCustomEvent","window","onTaskDeadLineChange","openAddForm","extractItemValue","idOffset","extractItemDisplay","openUpdateForm","item","items","open","data","createItem","Item","prepareData","getNodeByTemplate","append","auxData","taskId","dateString","stamp","type","isNotEmptyString","dateStringToStamp","setDeadLineStamp","node","update","syncAllIfCan","addItem","fireChangeEvent","parameters","callMethod","arguments","load","getDateDiff","from","to","isNumber","diff","unit","Math","floor","dateStampToString","format","d","Date","date","string","parsed","parseDate","parseInt","getTime","syncAll","q","getQuery","arg","k","push","add","SE_REMINDER","randomTag","round","random","bindEvents","bindDelegateControl","onEditClick","TRANSPORT","TYPE","REMIND_DATE","RECEPIENT_TYPE","value","VALUE","display","DISPLAY","innerHTML","REMINDER_TEXT","mergeEx","delete","remove","ctrls","e","PreventDefault","onDeleteClick","deleteItem","text","message","TRANSPORT_CLASS","Widget","REMINDER_TYPE_DEADLINE","REMINDER_TYPE_COMMON","deadLine","formatValue","convertThurlyFormat","setType","setRecipientType","setTransportType","setUnit","setMultiplier","setDate","reset","win","getWindow","changeCSSFlag","show","close","PopupWindow","autoHide","closeByEsc","content","overlay","lightShadow","closeIcon","draggable","titleBar","angle","position","offsetTop","offsetLeft","events","onPopupClose","onFormClose","bindControl","passCtx","onChangeType","onChangeRecipientType","onChangeTransportType","onSubmit","datepicker","isElementNode","dp","DatePicker","defaultTime","optionP","COMPANY_WORKTIME","HOURS","START","onChangeRemindDate","closeTypeSubWindow","setBindElement","toggleDeadLineMode","setPicker","setValue","setCSSMode","toLowerCase","noDeadline","getDeadLineOffset","mp","isNaN","seconds","length","fireEvent","clone","closeCalendarSubWindow","typeWindow","popupWindow","closeCalendar","self","menu","title","className","onclick","menuId","PopupMenu","onPopupShow","getMenuById"],"mappings":"AAAAA,GAAGC,UAAU,oBAEb,WAEC,SAAUD,IAAGE,MAAMC,UAAUC,yBAA2B,YACxD,CACC,OAGDJ,GAAGE,MAAMC,UAAUC,wBAA0BJ,GAAGE,MAAMG,KAAKC,QAAQC,QAClEC,KACCC,KAAM,YAEDC,WACIC,SAAU,IACVC,UAAW,KAErBC,SACCC,UAAW,WAEVC,KAAKC,cAAchB,GAAGE,MAAMG,KAAKC,QAErBS,MAAKE,KAAKC,cAAgB,CAE1BH,MAAKI,UAAUC,UAEfL,MAAKI,UAAUE,KAAO,GAAIC,IACtBC,MAAOR,KAAKS,QAAQ,QACpBC,GAAIV,KAAKU,KAAK,QACdC,OAAQX,MAGZA,MAAKY,UAAUZ,KAAKa,OAAO,UAC3Bb,MAAKc,gBAAgBd,KAAKa,OAAO,gBAEjCb,MAAKI,UAAUE,KAAKS,UAAU,OAAQ9B,GAAG+B,SAAShB,KAAKiB,YAAajB,MACpEA,MAAKe,UAAU,kBAAmB9B,GAAG+B,SAAShB,KAAKc,gBAAiBd,MAGpEf,IAAGiC,eAAeC,OAAQ,+BAAgClC,GAAG+B,SAAShB,KAAKoB,qBAAsBpB,MACjGf,IAAGiC,eAAeC,OAAQ,4BAA6BlC,GAAG+B,SAAShB,KAAKqB,YAAarB,QAGlGsB,iBAAkB,WAEjB,MAAOtB,MAAKE,KAAKqB,YAGlBC,mBAAoB,WAEnB,MAAO,KAGCC,eAAgB,SAASf,GAErB,GAAIgB,GAAO1B,KAAKE,KAAKyB,MAAMjB,EAC3BV,MAAKE,KAAKC,cAAgBO,CAE1BV,MAAKI,UAAUE,KAAKsB,KAAKF,EAAKlB,QAASkB,EAAKxB,KAAK2B,OAG9DC,WAAY,SAASD,GAEpBA,EAAOE,EAAKC,YAAYH,EAGxB,IAAIrB,GAAQR,KAAKiC,kBAAkB,OAAQJ,GAAM,EAEjD5C,IAAGiD,OAAO1B,EAAOR,KAAKS,QAAQ,SAG9B,IAAIiB,GAAO,GAAIK,IACdvB,MAAOA,EACPqB,KAAMA,EACNM,QAASnC,KAAKa,OAAO,WACNF,OAAQX,MAGxB,OAAO0B,IAGCd,UAAW,SAASwB,GAEhBpC,KAAKE,KAAKkC,OAASA,EAASA,EAAS,GAGzCtB,gBAAiB,SAASuB,GAEtB,GAAIC,GAAQ,CACZ,IAAGrD,GAAGsD,KAAKC,iBAAiBH,GAC5B,CACIC,EAAQtC,KAAKyC,kBAAkBJ,GAGnCrC,KAAKI,UAAUE,KAAKoC,iBAAiBJ,IAGlDjB,YAAa,SAASsB,GAET3C,KAAKE,KAAKC,cAAgB,CAC1BH,MAAKI,UAAUE,KAAKsB,KAAKe,IAG7BvB,qBAAsB,SAASgB,EAAQC,GAEnC,GAAGD,GAAUpC,KAAKE,KAAKkC,OACvB,CACIpC,KAAKc,gBAAgBuB,KAI7BpB,YAAa,SAASY,GAElB,GAAG7B,KAAKE,KAAKC,eAAiB,EAC9B,CACIH,KAAKE,KAAKyB,MAAM3B,KAAKE,KAAKC,eAAeyC,OAAOf,EAChD7B,MAAK6C,mBAGT,CACI7C,KAAK8C,QAAQjB,KAK9BkB,gBAAiB,SAASC,GAEzBhD,KAAKiD,WAAWhE,GAAGE,MAAMG,KAAKC,QAAS,kBAAmB2D,UAE1D,KAAIF,EAAWG,KACf,CACCnD,KAAK6C,iBAYEO,YAAa,SAASC,EAAMC,GAExB,IAAIrE,GAAGsD,KAAKgB,SAASF,GACrB,CACIA,EAAOrD,KAAKyC,kBAAkBY,GAElC,IAAIpE,GAAGsD,KAAKgB,SAASD,GACrB,CACIA,EAAKtD,KAAKyC,kBAAkBa,GAGhC,GAAIE,GAAOF,EAAKD,CAChB,IAAII,GAAOzD,KAAKH,SAChB,IAAG2D,EAAO,OAAS,EACnB,CACIC,EAAOzD,KAAKJ,QACZ4D,GAAOE,KAAKC,MAAMH,EAAO,WAG7B,CACIA,EAAOE,KAAKC,MAAMH,EAAO,MAG7B,OAAQA,KAAMA,EAAMC,KAAMA,IAG9BG,kBAAmB,SAAStB,EAAOuB,GAE/B,GAAIC,GAAI,GAAIC,MAAKzB,EAAQ,IAEzB,OAAOrD,IAAG+E,KAAKH,OAAOA,EAAQC,EAAG,MAAO,OAG5CrB,kBAAmB,SAASwB,GAExB,GAAIC,GAASjF,GAAGkF,UAAUF,EAAQ,KAClC,IAAGC,GAAU,KACb,CACI,MAAOR,MAAKC,MAAOS,SAASF,EAAOG,WAAa,KAGpD,MAAO,IAGXC,QAAS,WAEL,GAAIC,GAAIvE,KAAKwE,UACb,IAAGD,GAAKH,SAASpE,KAAKa,OAAO,WAC7B,CACI,GAAI4D,KACJ,KAAI,GAAIC,KAAK1E,MAAKE,KAAKyB,MACvB,CACI8C,EAAIE,KAAK3E,KAAKE,KAAKyB,MAAM+C,GAAG7C,QAGhC0C,EAAEK,IAAI,eACFlE,GAAI0D,SAASpE,KAAKa,OAAO,WACzBgB,MACIgD,YAAaJ,KAGjB/E,KAAM,wBAO7B,IAAIqC,GAAO9C,GAAGE,MAAMG,KAAKC,QAAQwC,KAAKvC,QACrCC,KACCC,KAAM,QAEPI,SACCC,UAAW,WAEVC,KAAKC,cAAchB,GAAGE,MAAMG,KAAKC,QAAQwC,KAEzC/B,MAAKE,KAAK2B,KAAO7B,KAAKa,OAAO,OAC7Bb,MAAKE,KAAK4E,UAAYpB,KAAKqB,MAAMrB,KAAKsB,SAAW,IAEjDhF,MAAKiF,cAGNA,WAAY,WAEXjF,KAAKkF,oBAAoB,OAAQ,QAASjG,GAAG+B,SAAShB,KAAKmF,YAAanF,QAGzE6B,KAAM,WAEL,OACCuD,UAAWpF,KAAKE,KAAK2B,KAAKuD,UAC1BC,KAAMrF,KAAKE,KAAK2B,KAAKwD,KACrBC,YAAatF,KAAKE,KAAK2B,KAAKyD,YAC5BC,eAAgBvF,KAAKE,KAAK2B,KAAK0D,iBAIjCC,MAAO,WAEN,MAAOxF,MAAKE,KAAK2B,KAAK4D,OAEvBC,QAAS,WAER,MAAO1F,MAAKE,KAAK2B,KAAK8D,SAGvB/C,OAAQ,SAASf,GAGhBA,EAAOE,EAAKC,YAAYH,EAExB7B,MAAKS,QAAQ,aAAa+E,MAAQ3D,EAAKuD,SACvCpF,MAAKS,QAAQ,QAAQ+E,MAAQ3D,EAAKwD,IAClCrF,MAAKS,QAAQ,eAAe+E,MAAQ3D,EAAKyD,WACzCtF,MAAKS,QAAQ,kBAAkB+E,MAAQ3D,EAAK0D,cAC5CvF,MAAKS,QAAQ,QAAQmF,UAAY/D,EAAKgE,aAEtC5G,IAAG4C,EAAKuD,WAAa,IAAM,WAAa,eAAepF,KAAKS,QAAQ,QAAS,cAE7ET,MAAKE,KAAK2B,KAAO5C,GAAG6G,QAAQ9F,KAAKE,KAAK2B,KAAMA,IAG7CkE,SAAQ,WAEP,GAAIP,GAAQxF,KAAKwF,OAEjBvG,IAAG+G,OAAOhG,KAAKP,IAAIe,MACnBR,MAAKP,IAAIe,MAAQ,IACjBR,MAAKiG,MAAQ,IACbjG,MAAKE,KAAK2B,KAAO,IAEjB,OAAO2D,IAGRL,YAAa,SAASe,GAErBlG,KAAKW,SAASc,eAAezB,KAAKwF,QAClCvG,IAAGkH,eAAeD,IAGnBE,cAAe,SAASF,GAGvBlG,KAAKW,SAAS0F,WAAWrG,KAAKwF,QAC9BvG,IAAGkH,eAAeD,MAIrBnE,GAAKC,YAAc,SAASH,GAI3B,GAAIyE,GAAO,EAEX,IAAGzE,EAAK0D,gBAAkB,KAAO1D,EAAK0D,gBAAkB,KAAO1D,EAAK0D,gBAAkB,IACtF,CACCe,EAAOrH,GAAGsH,QAAQ,+CAA+C1E,EAAK0D,gBAGvE1D,EAAKgE,cAAgBhE,EAAKyD,YAAY,IAAIgB,CAC1CzE,GAAK2E,gBAAkB3E,EAAKuD,WAAa,IAAM,cAAgB,EAE/D,OAAOvD,GAGR,IAAItB,GAAOtB,GAAGE,MAAMG,KAAKmH,OAAOjH,QAC/BC,KACCC,KAAM,QAEPC,WACC+G,uBAAwB,IACxBC,qBAAsB,KAEvB7G,SAECC,UAAW,WAEVC,KAAKC,cAAchB,GAAGE,MAAMG,KAAKmH,OAGjCzG,MAAKE,KAAK2B,MACTwD,KAAM,IACNE,eAAgB,IAChBD,YAAa,GACbF,UAAW,IAEZpF,MAAKE,KAAK0G,SAAW,CACrB5G,MAAKE,KAAK2G,YAAc5H,GAAG+E,KAAK8C,oBAAoB7H,GAAGsH,QAAQ,qBAGhEpD,KAAM,SAAStB,GAEd,IAAI5C,GAAGsD,KAAKC,iBAAiBX,EAAKyD,aAClC,CACC,OAGDtF,KAAK+G,QAAQlF,EAAKwD,KAClBrF,MAAKgH,iBAAiBnF,EAAK0D,eAC3BvF,MAAKiH,iBAAiBpF,EAAKuD,UAE3B,IAAGvD,EAAKwD,MAAQrF,KAAK0G,uBACrB,CAEC,GAAIlD,GAAOxD,KAAKW,SAASyC,YAAYvB,EAAKyD,YAAatF,KAAKE,KAAK0G,SACjE5G,MAAKkH,QAAQ1D,EAAKC,KAClBzD,MAAKmH,cAAc3D,EAAKA,UAGzB,CACCxD,KAAKoH,QAAQvF,EAAKyD,YAAa,QAIjC+B,MAAO,WAENrH,KAAK+G,QAAQ,IACb/G,MAAKgH,iBAAiB,IACtBhH,MAAKoH,QAAQ,GAAI,KACjBpH,MAAKiH,iBAAiB,IAEtBjH,MAAKmH,cAAc,EACnBnH,MAAKkH,QAAQlH,KAAKW,SAASf,WAG5BgC,KAAM,SAASe,EAAMd,GAEpB,GAAIyF,GAAMtH,KAAKuH,UAAU5E,EAEzB,UAAUd,IAAQ,YAClB,CACC7B,KAAKmD,KAAKtB,OAGX,CACC7B,KAAKqH,QAENrH,KAAKwH,cAAc,oBAAsB3F,IAAQ,YACjDyF,GAAIG,QAELC,MAAO,WAEN1H,KAAKuH,YAAYG,OACjB1H,MAAKqH,SAGNE,UAAW,SAAS5E,GAEnB,SAAU3C,MAAKI,UAAUe,QAAU,YACnC,CACCnB,KAAKI,UAAUe,OAAS,GAAIlC,IAAG0I,YAAY3H,KAAKU,KAAMiC,GACrDiF,SAAU,KACVC,WAAY,KACZC,QAAS9H,KAAKQ,QACduH,QAAS,MACTC,YAAa,KACbC,UAAW,KACXC,UAAW,MACXC,SAAU,MACVC,OAAQC,SAAU,OAClBC,UAAW,GACXC,WAAY,GACZC,QACCC,aAAcxJ,GAAG+B,SAAShB,KAAK0I,YAAa1I,QAK9CA,MAAK2I,YAAY,cAAe,QAAS3I,KAAK4I,QAAQ5I,KAAK6I,cAC3D7I,MAAK2I,YAAY,mBAAoB,SAAU3I,KAAK4I,QAAQ5I,KAAK8I,uBACjE9I,MAAKkF,oBAAoB,mBAAoB,QAASlF,KAAK4I,QAAQ5I,KAAK+I,uBACxE/I,MAAK2I,YAAY,SAAU,QAAS1J,GAAG+B,SAAShB,KAAKgJ,SAAUhJ,OAGhE,SAAUA,MAAKI,UAAU6I,YAAc,YACvC,CACC,GAAIzI,GAAQR,KAAKS,QAAQ,OACzB,IAAGxB,GAAGsD,KAAK2G,cAAc1I,GACzB,CACC,GAAI2I,GAAK,GAAIlK,IAAGE,MAAMG,KAAK8J,YAC1B5I,MAAOA,EACP6I,YAAarJ,KAAKsJ,QAAQ,WAAWC,iBAAiBC,MAAMC,OAE7DN,GAAGpI,UAAU,SAAU9B,GAAG+B,SAAShB,KAAK0J,mBAAoB1J,MAC5DmJ,GAAGpI,UAAU,OAAQ9B,GAAG+B,SAAShB,KAAK2J,mBAAoB3J,MAE1DA,MAAKI,UAAU6I,WAAaE,GAI9B,GAAGlK,GAAGsD,KAAK2G,cAAcvG,GACzB,CACC3C,KAAKI,UAAUe,OAAOyI,eAAejH,GAGtC,MAAO3C,MAAKI,UAAUe,QAGvBuB,iBAAkB,SAASJ,GAE1BtC,KAAKE,KAAK0G,SAAWtE,CACrBtC,MAAK6J,sBAGNH,mBAAoB,SAASpH,EAAO2B,GAEnCjE,KAAKoH,QAAQnD,EAAQ,QAGtBkD,cAAe,SAAS3B,GAEvBxF,KAAKS,QAAQ,mBAAmB+E,MAAQA,GAGzC0B,QAAS,SAAS1B,GAEjBxF,KAAKS,QAAQ,aAAa+E,MAAQA,GAGnC4B,QAAS,SAAS5B,EAAOsE,GAExB,SAAUtE,IAAS,YACnB,CACC,OAGDxF,KAAKE,KAAK2B,KAAKyD,YAAcE,CAC7B,IAAGsE,EACH,CACC9J,KAAKI,UAAU6I,WAAWc,SAASvE,KAIrCuB,QAAS,SAASxE,GAEjB,IAAIA,EACJ,CACC,OAGDvC,KAAKE,KAAK2B,KAAKwD,KAAO9C,CACtBvC,MAAKgK,WAAW,OAAQzH,EAAK0H,gBAG9BjD,iBAAkB,SAASzE,GAE1B,IAAIA,EACJ,CACC,OAGDvC,KAAKE,KAAK2B,KAAK0D,eAAiBhD,CAChCvC,MAAKS,QAAQ,oBAAoB+E,MAAQjD,GAG1C0E,iBAAkB,SAAS1E,GAE1B,IAAIA,EACJ,CACC,OAGDvC,KAAKE,KAAK2B,KAAKuD,UAAY7C,CAC3BvC,MAAKgK,WAAW,YAAazH,EAAK0H,gBAGnCJ,mBAAoB,WAEnB,GAAIK,GAAalK,KAAKE,KAAK0G,UAAY,CAEvC,IAAIsD,EACJ,CAEC,GAAGlK,KAAKE,KAAK2B,KAAKwD,MAAQrF,KAAK0G,uBAC/B,CACC1G,KAAK+G,QAAQ/G,KAAK2G,qBAClB3G,MAAKoH,QAAQ,EAAG,KAIlBpH,KAAKwH,cAAc,cAAe0C,IAGnCC,kBAAmB,WAElB,GAAIC,GAAKhG,SAASpE,KAAKS,QAAQ,mBAAmB+E,MAClD,IAAG6E,MAAMD,GACT,CACCA,EAAK,EAEN,GAAIE,GAAUtK,KAAKS,QAAQ,aAAa+E,OAAS,IAAM,MAAQ,IAE/D,OAAOxF,MAAKW,SAASiD,kBAAkB5D,KAAKE,KAAK0G,SAAWwD,EAAGE,EAAStK,KAAKE,KAAK2G,cAGnFmC,SAAU,WAET,GAAGhJ,KAAKE,KAAK2B,KAAKwD,MAAQrF,KAAK0G,uBAC/B,CAEC1G,KAAKE,KAAK2B,KAAKyD,YAActF,KAAKmK,oBAGnC,GAAGnK,KAAKE,KAAK2B,KAAKyD,YAAYiF,QAAU,GACxC,CACC,OAGDvK,KAAKwK,UAAU,QAASvL,GAAGwL,MAAMzK,KAAKE,KAAK2B,OAC3C7B,MAAK0H,SAGNgB,YAAa,WAGZ1I,KAAK2J,oBACL3J,MAAK0K,0BAGNf,mBAAoB,WAEnB,SAAU3J,MAAKI,UAAUuK,YAAc,aAAe3K,KAAKI,UAAUuK,YAAc,KACnF,CACC3K,KAAKI,UAAUuK,WAAWC,YAAYlD,UAIxCgD,uBAAwB,WAEvB,SAAU1K,MAAKI,UAAU6I,YAAc,aAAejJ,KAAKI,UAAU6I,YAAc,KACnF,CACCjJ,KAAKI,UAAU6I,WAAW4B,kBAI5B9B,sBAAuB,SAASpG,GAE/B,GAAIJ,GAAOtD,GAAG4C,KAAKc,EAAM,YACzB,UAAUJ,IAAQ,aAAeA,GAAQ,KACzC,CACCvC,KAAKiH,iBAAiB1E,KAIxBuG,sBAAuB,SAASnG,GAE/B3C,KAAKgH,iBAAiBrE,EAAK6C,QAG5BqD,aAAc,SAASlG,GAEtB,GAAImI,GAAO9K,IAEX,IAAI+K,KAEFzE,KAAMrH,GAAGsH,QAAQ,uCACjByE,MAAO/L,GAAGsH,QAAQ,0CAClB0E,UAAW,qBACXC,QAAS,WACRJ,EAAK/D,QAAQ,IACb/G,MAAK4K,YAAYlD,WAIlBpB,KAAMrH,GAAGsH,QAAQ,uCACjByE,MAAO/L,GAAGsH,QAAQ,0CAClB0E,UAAW,qBACXC,QAAS,WACRJ,EAAK/D,QAAQ,IACb/G,MAAK4K,YAAYlD,UAKpB,IAAIyD,GAASnL,KAAKU,KAAK,iBACvBzB,IAAGmM,UAAU3D,KAAK0D,EAAQxI,EAAMoI,GAAOzC,UAAY,EAAGE,QACrD6C,YAAapM,GAAG+B,SAAShB,KAAK0K,uBAAwB1K,QAEvDA,MAAKI,UAAUuK,WAAa1L,GAAGmM,UAAUE,YAAYH"}