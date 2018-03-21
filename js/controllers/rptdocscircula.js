(function(){

    var rptdocscirculactrl = angular.module('cpm.rptdocscirculactrl', []);

    rptdocscirculactrl.controller('rptDocsCirculaCtrl', ['$scope', 'tranBancSrvc', 'authSrvc', 'bancoSrvc', 'empresaSrvc', function($scope, tranBancSrvc, authSrvc, bancoSrvc, empresaSrvc){

        $scope.objEmpresa = {};
        $scope.losBancos = [];
        $scope.params = { idempresa: 0, fAl: moment().toDate(), idbanco: 0, falstr:'' };
        $scope.losDocs = [];
        $scope.objBanco = [];
        $scope.data = [];
        $scope.resumen = [];

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(d){
                    $scope.objEmpresa = d[0];
                    $scope.params.idempresa = parseInt($scope.objEmpresa.id);
                    bancoSrvc.lstBancos($scope.params.idempresa).then(function(d) {
                        $scope.losBancos = d;
                        $scope.losBancos.push({id:0, bancomoneda: 'Todos los bancos'});
                    });
                });
            }
        });

        $scope.getDocsCirculando = function(){
            $scope.params.idbanco = $scope.objBanco[0] !== null && $scope.objBanco[0] !== undefined ? ($scope.objBanco.length == 1 ? $scope.objBanco[0].id : 0) : 0;
            $scope.params.falstr = moment($scope.params.fAl).format('YYYY-MM-DD');
            tranBancSrvc.rptdocscircula($scope.params).then(function(d){
                $scope.losDocs = d;
                //console.log($scope.losDocs);
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
                        descripcion: $scope.losDocs[x].descripcion
                    });
                };
            };
            return uniqueTiposDoc;
        };

        $scope.styleData = function(){
            $scope.data = [];
            var qBancos = getBancos();
            var qTiposDoc = getTiposDoc();
            var tmp = {};
            var sumas = {totMonto: 0.0};

            for(var i = 0; i < qBancos.length; i++){
                $scope.data.push({
                    idbanco: qBancos[i].idbanco,
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
                            descripcion: qTiposDoc[j].descripcion,
                            docs: []
                        });
                    };
                };
            };

            for(var i = 0; i < $scope.data.length; i++){
                for(var j = 0; j < $scope.data[i].tdocs.length; j++){
                    var sumas = {totMonto: 0.0};
                    for(var k = 0; k < $scope.losDocs.length; k++){
                        tmp = $scope.losDocs[k];
                        if(parseInt(tmp.idbanco) === $scope.data[i].tdocs[j].idbanco && tmp.abreviatura === $scope.data[i].tdocs[j].abreviatura){
                            $scope.data[i].tdocs[j].docs.push({
                                fecha: moment(tmp.fecha).toDate(),
                                numero: tmp.numero,
                                beneficiario: tmp.beneficiario,
                                concepto: tmp.concepto,
                                monto: parseFloat(tmp.monto)
                            });
                            sumas.totMonto += parseFloat(tmp.monto);
                        };
                    };
                    $scope.data[i].tdocs[j].docs.push({
                        fecha: '',
                        numero: '',
                        beneficiario: 'Total de ' + $scope.data[i].tdocs[j].descripcion,
                        concepto: '--->',
                        monto: sumas.totMonto
                    });
                };
            };

            for(var i = 0; i < $scope.losDocs.length; i++){
                tmp = $scope.losDocs[i];
                if(parseInt(tmp.idbanco) === 0){
                    $scope.resumen.push({
                        abreviatura: tmp.abreviatura,
                        descripcion: tmp.descripcion,
                        monto: tmp.monto
                    });
                };
            };
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Documentos en circulación');
        };

    }]);

}());
