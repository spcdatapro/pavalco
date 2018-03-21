(function(){

    var rptcorrchctrl = angular.module('cpm.rptcorrchctrl', []);

    rptcorrchctrl.controller('rptCorrChCtrl', ['$scope', 'tranBancSrvc', 'authSrvc', 'bancoSrvc', 'empresaSrvc', function($scope, tranBancSrvc, authSrvc, bancoSrvc, empresaSrvc){

        $scope.objEmpresa = {};
        $scope.losBancos = [];
        $scope.params = { idempresa: 0, fDel: moment().startOf('month').toDate(), fAl: moment().endOf('month').toDate(), idbanco: 0,
            fdelstr: '', falstr:'' };
        $scope.losCheques = [];
        $scope.objBanco = [];
        $scope.data = [];

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

        $scope.getCorrelativosCheques = function(){
            $scope.params.idbanco = $scope.objBanco[0] !== null && $scope.objBanco[0] !== undefined ? ($scope.objBanco.length == 1 ? $scope.objBanco[0].id : 0) : 0;
            $scope.params.fdelstr = moment($scope.params.fDel).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.fAl).format('YYYY-MM-DD');
            tranBancSrvc.rptcorrch($scope.params).then(function(d){
                $scope.losCheques = d;
                //console.log($scope.losCheques);
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

        /*
        function indexOfCompra(myArray, searchTerm) {
            var index = -1;
            for(var i = 0, len = myArray.length; i < len; i++) {
                if (myArray[i].idcompra === searchTerm) {
                    index = i;
                    break;
                }
            }
            return index;
        };
        */

        function getBancos(){
            var uniqueBancos = [];
            for(var x = 0; x < $scope.losCheques.length; x++){
                if(indexOfBanco(uniqueBancos, parseInt($scope.losCheques[x].idbanco)) < 0){
                    uniqueBancos.push({
                        idbanco: parseInt($scope.losCheques[x].idbanco),
                        nombre: $scope.losCheques[x].banco
                    });
                };
            };
            return uniqueBancos;
        };

        /*
        function getCompras(){
            var uniqueCompras = [];
            for(var x = 0; x < $scope.losPagos.length; x++){
                if(indexOfCompra(uniqueCompras, parseInt($scope.losPagos[x].idcompra)) < 0){
                    uniqueCompras.push({
                        idprov: parseInt($scope.losPagos[x].idprov),
                        idcompra: parseInt($scope.losPagos[x].idcompra),
                        documento: $scope.losPagos[x].documento,
                        totfact: parseFloat($scope.losPagos[x].totfact)
                    });
                };
            };
            return uniqueCompras;
        };
        */

        $scope.styleData = function(){
            $scope.data = [];
            var qBancos = getBancos();
            //var qCompras = getCompras();
            var tmp = {};
            var sumas = {totMonto: 0.0};

            for(var i = 0; i < qBancos.length; i++){
                $scope.data.push({
                    idbanco: qBancos[i].idbanco,
                    nombre: qBancos[i].nombre,
                    cheques: []
                });
            };

            for(var i = 0; i < $scope.data.length; i++){
                sumas = {totMonto: 0.0};
                for(var j = 0; j < $scope.losCheques.length; j++){
                    tmp = $scope.losCheques[j];
                    if(parseInt(tmp.idbanco) === $scope.data[i].idbanco){
                        $scope.data[i].cheques.push({
                            numero: tmp.numero,
                            fecha: moment(tmp.fecha).toDate(),
                            beneficiario: tmp.beneficiario,
                            concepto: tmp.concepto,
                            monto: parseFloat(tmp.monto)
                        });
                        sumas.totMonto += parseFloat(tmp.monto);
                    };
                };
                $scope.data[i].cheques.push({
                    numero: '',
                    fecha: '',
                    beneficiario: 'Total',
                    concepto: '--->',
                    monto: sumas.totMonto
                });
            };
            //console.log($scope.data);
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Correlativo de cheques');
        };

    }]);

}());
