(function(){

    var rptdetcontdocsctrl = angular.module('cpm.rptdetcontdocsctrl', []);

    rptdetcontdocsctrl.controller('rptDetContDocsCtrl', ['$scope', 'detContSrvc', 'authSrvc', 'bancoSrvc', 'empresaSrvc', 'tipoMovTranBanSrvc', function($scope, detContSrvc, authSrvc, bancoSrvc, empresaSrvc, tipoMovTranBanSrvc){

        $scope.objEmpresa = {};
        $scope.losBancos = [];
        $scope.tipotrans = [];
        $scope.params = { idempresa: 0, fDel: moment().startOf('month').toDate(), fAl: moment().endOf('month').toDate(), idbanco: 0,
            abreviatura: '', fdelstr: '', falstr:'' };
        $scope.losDocs = [];
        $scope.objBanco = [];
        $scope.objTipotrans = {};
        $scope.data = [];

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(d){
                    $scope.objEmpresa = d[0];
                    $scope.params.idempresa = parseInt($scope.objEmpresa.id);
                    bancoSrvc.lstBancos(parseInt($scope.objEmpresa.id)).then(function(d) {
                        $scope.losBancos = d;
                        $scope.losBancos.push({id:0, bancomoneda: 'Todos los bancos'});
                    });
                });
            }
        });

        tipoMovTranBanSrvc.lstTiposMovTB().then(function(d){ $scope.tipotrans = d; });

        $scope.getDetContDocs = function(){
            $scope.params.idbanco = $scope.objBanco[0] !== null && $scope.objBanco[0] !== undefined ? ($scope.objBanco.length == 1 ? $scope.objBanco[0].id : 0) : 0;
            $scope.params.abreviatura = $scope.objTipotrans.abreviatura !== null && $scope.objTipotrans.abreviatura !== undefined ? $scope.objTipotrans.abreviatura : '';
            $scope.params.fdelstr = moment($scope.params.fDel).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.fAl).format('YYYY-MM-DD');
            detContSrvc.rptDetContDocs($scope.params).then(function(d){
                $scope.losDocs = d;
                //console.log($scope.elDetalle);
                $scope.styleData();
            });
        };
        function indexOfBanco(myArray, searchTerm) {
            var index = -1;
            for(var i = 0, len = myArray.length; i < len; i++) {
                if (myArray[i].idbanco === searchTerm) {
                    index = i;
                    break;
                }
            }
            return index;
        };

        function indexOfTiposDoc(myArray, searchTerm, searchBanco) {
            var index = -1;
            for(var i = 0, len = myArray.length; i < len; i++) {
                if (myArray[i].abreviatura === searchTerm && myArray[i].idbanco === searchBanco) {
                    index = i;
                    break;
                }
            }
            return index;
        };

        function indexOfDocs(myArray, searchTerm, searchBanco, searchTran) {
            var index = -1;
            for(var i = 0, len = myArray.length; i < len; i++) {
                if (myArray[i].abreviatura === searchTerm && myArray[i].idbanco === searchBanco && myArray[i].idtran === searchTran) {
                    index = i;
                    break;
                }
            }
            return index;
        };

        function getBancos(){
            var uniqueBancos = [];
            for(var x = 0; x < $scope.losDocs.length; x++){
                if(indexOfBanco(uniqueBancos, parseInt($scope.losDocs[x].idbanco)) < 0 && parseInt($scope.losDocs[x].idbanco) > 0){
                    uniqueBancos.push({
                        idbanco: parseInt($scope.losDocs[x].idbanco),
                        nombre: $scope.losDocs[x].banco
                    });
                };
            };
            return uniqueBancos;
        };

        function getTiposDoc(){
            var uniqueTiposDoc = [];
            for(var x = 0; x < $scope.losDocs.length; x++){
                if(indexOfTiposDoc(uniqueTiposDoc, $scope.losDocs[x].abreviatura, parseInt($scope.losDocs[x].idbanco)) < 0 && parseInt($scope.losDocs[x].idbanco) > 0){
                    uniqueTiposDoc.push({
                        idbanco: parseInt($scope.losDocs[x].idbanco),
                        abreviatura: $scope.losDocs[x].abreviatura,
                        tipo: $scope.losDocs[x].tipo
                    });
                };
            };
            return uniqueTiposDoc;
        };

        function getDocs(){
            var uniqueDocs = [];
            for(var x = 0; x < $scope.losDocs.length; x++){
                if(indexOfDocs(uniqueDocs, $scope.losDocs[x].abreviatura, parseInt($scope.losDocs[x].idbanco), parseInt($scope.losDocs[x].idtran)) < 0){
                    uniqueDocs.push({
                        idbanco: parseInt($scope.losDocs[x].idbanco),
                        abreviatura: $scope.losDocs[x].abreviatura,
                        idtran: parseInt($scope.losDocs[x].idtran),
                        fecha: moment($scope.losDocs[x].fecha).toDate(),
                        numero: $scope.losDocs[x].numero,
                        beneficiario: $scope.losDocs[x].beneficiario,
                        concepto: $scope.losDocs[x].concepto,
                        monto: parseFloat($scope.losDocs[x].monto)
                    });
                };
            };
            return uniqueDocs;
        };

        $scope.styleData = function(){
            $scope.data = [];
            var qBancos = getBancos(), qTiposDoc = getTiposDoc(), qDocs = getDocs();
            var tmp = {}, sumas = {totDebe: 0.0, totHaber: 0.0};

            for(var i = 0; i < qBancos.length; i++){
                $scope.data.push({
                    idbanco: parseInt(qBancos[i].idbanco),
                    nombre: qBancos[i].nombre,
                    tdocs: []
                });
            };

            for(var i = 0; i < $scope.data.length; i++){
                for(var j = 0; j < qTiposDoc.length; j++){
                    if(qTiposDoc[j].idbanco === parseInt($scope.data[i].idbanco)){
                        $scope.data[i].tdocs.push({
                            idbanco: parseInt(qTiposDoc[j].idbanco),
                            abreviatura: qTiposDoc[j].abreviatura,
                            tipo: qTiposDoc[j].tipo,
                            docs: []
                        });
                    };
                };
            };

            var n1 = {}, n2= {}, n3 = {}, n4 = {};
            for(var i = 0; i < $scope.data.length; i++){
                n1 = $scope.data[i];
                for(var j = 0; j < n1.tdocs.length; j++){
                    n2 = n1.tdocs[j];
                    for(var k = 0; k < qDocs.length; k++){
                        n3 = qDocs[k];
                        if(n3.idbanco === n1.idbanco && n3.abreviatura === n2.abreviatura){
                            $scope.data[i].tdocs[j].docs.push({
                                idbanco: n1.idbanco,
                                abreviatura: n2.abreviatura,
                                idtran: n3.idtran,
                                fecha: n3.fecha,
                                numero: n3.numero,
                                beneficiario: n3.beneficiario,
                                concepto: n3.concepto,
                                monto: n3.monto,
                                detcont: []
                            });
                        };
                    };
                };
            };

            for(var i = 0; i < $scope.data.length; i++){
                n1 = $scope.data[i];
                for(var j = 0; j < n1.tdocs.length; j++){
                    n2 = n1.tdocs[j];
                    for(var k = 0; k < n2.docs.length; k++){
                        n3 = n2.docs[k];
                        sumas = {totDebe: 0.0, totHaber: 0.0};
                        for(var l = 0; l < $scope.losDocs.length; l++){
                            n4 = $scope.losDocs[l];
                            if(parseInt(n4.idtran) === n3.idtran){
                                $scope.data[i].tdocs[j].docs[k].detcont.push({
                                    codigo: n4.codigo,
                                    nombrecta: n4.nombrecta,
                                    debe: parseFloat(n4.debe),
                                    haber: parseFloat(n4.haber),
                                    conceptomayor: n4.conceptomayor
                                });
                                sumas.totDebe += parseFloat(n4.debe);
                                sumas.totHaber += parseFloat(n4.haber);
                            };
                        };
                        $scope.data[i].tdocs[j].docs[k].detcont.push({
                            codigo: 'Total',
                            nombrecta: '--->',
                            debe: sumas.totDebe,
                            haber: sumas.totHaber,
                            conceptomayor: 'Partida ' + ((sumas.totDebe === sumas.totHaber) ? 'cuadrada' : 'descuadrada')
                        });
                    };
                };
            };

            console.log($scope.data);
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Detalle contable de documentos');
        };

    }]);

}());
