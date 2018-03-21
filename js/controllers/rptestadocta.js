(function(){

    var rptestadoctactrl = angular.module('cpm.rptestadoctactrl', []);

    rptestadoctactrl.controller('rptEstadoCtaCtrl', ['$scope', 'authSrvc', 'bancoSrvc', 'empresaSrvc', function($scope, authSrvc, bancoSrvc, empresaSrvc){

        $scope.objEmpresa = {};
        $scope.losBancos = [];
        $scope.params = {
            idempresa: 0, fDel: moment().startOf('month').toDate(), fAl: moment().endOf('month').toDate(), idbanco: 0, fdelstr: '', falstr:'',
            contc:1, quetzalizado: 1
        };
        $scope.objBanco = [];
        $scope.data = {};
        $scope.dectc = 2;

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(d){
                    $scope.objEmpresa = d[0];
                    $scope.dectc = parseInt($scope.objEmpresa.dectc);
                    $scope.params.idempresa = parseInt($scope.objEmpresa.id);
                    bancoSrvc.lstBancos(parseInt($scope.objEmpresa.id)).then(function(d) {
                        $scope.losBancos = d;                        
                    });
                });
            }
        });

        $scope.getData = function(){
            $scope.params.idbanco = $scope.objBanco[0].id;            
            $scope.params.fdelstr = moment($scope.params.fDel).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.fAl).format('YYYY-MM-DD');
            $scope.params.contc = $scope.params.contc != null && $scope.params.contc != undefined ? $scope.params.contc : 0;
            $scope.params.quetzalizado = $scope.params.quetzalizado != null && $scope.params.quetzalizado != undefined ? $scope.params.quetzalizado : 0;
            bancoSrvc.rptEstadoCta($scope.params).then(function(d){
                $scope.data = d;
            });
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Estado de cuenta del banco ' + $scope.objBanco[0].nombre);
        };

    }]);

}());
