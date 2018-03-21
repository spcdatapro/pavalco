(function(){

    var rptintegraclictrl = angular.module('cpm.rptintegraclictrl', []);

    rptintegraclictrl.controller('rptIntegraClientesCtrl', ['$scope', 'empresaSrvc', 'authSrvc', 'jsReportSrvc', '$sce', 'cuentacSrvc', function($scope, empresaSrvc, authSrvc, jsReportSrvc, $sce, cuentacSrvc){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), idempresa: 0, idcuenta: ''};
        $scope.cuentas = [];

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                $scope.params.idempresa = parseInt(usrLogged.workingon);
                cuentacSrvc.getByTipo($scope.params.idempresa, 0).then(function(d){ $scope.cuentas = d; });
            }
        });

        $scope.getIntegracion = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');

            jsReportSrvc.integracionClientes($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $scope.content = $sce.trustAsResourceUrl(fileURL);
            });

        };

        /*
        $scope.getLibroMayorXLSX = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.codigo = $scope.params.codigo != null && $scope.params.codigo != undefined ? $scope.params.codigo : '';

            jsReportSrvc.libroMayorXlsx($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                saveAs(file, 'LibroMayor.xlsx');
            });

        };
        */

        //$scope.printVersion = function(){ PrintElem('#toPrint', 'Libro Mayor'); };

    }]);

}());
