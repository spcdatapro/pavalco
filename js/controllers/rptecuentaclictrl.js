(function(){

    var rptecuentaclictrl = angular.module('cpm.rptecuentaclictrl', []);

    rptecuentaclictrl.controller('rptEcuentaClientesCtrl', ['$scope', 'rptEcuentaClientesSrvc', 'authSrvc', 'jsReportSrvc', '$sce','clienteSrvc', function($scope, rptEcuentaClientesSrvc, authSrvc, jsReportSrvc, $sce,clienteSrvc){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), idempresa: 0,detalle: 0, cliente: {id: 0}};
        $scope.ecuentacliente = [];
        $scope.content = undefined;
        $scope.clientes = [];

        clienteSrvc.lstAllClientes().then(function(d){
            $scope.clientes = d;
        });

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                //authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
                $scope.params.idempresa = parseInt(usrLogged.workingon);
            }
        });

        $scope.resetData = function(){
            $scope.ecuentacliente = [];
        };

        $scope.getEcuentaCli = function(){
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.clistr = $scope.params.cliente.id;

            jsReportSrvc.ecuentaClientes($scope.params).then(function (result) {
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $scope.content = $sce.trustAsResourceUrl(fileURL);
            });

        };

        $scope.getEcuentaCliXLSX = function(){
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.clistr = $scope.params.cliente.id;

            jsReportSrvc.ecuentaClientesXlsx($scope.params).then(function (result) {
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                saveAs(file, 'EcuentaClientes.xlsx');
            });
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Estado de Cuenta de Clientes');
        };

    }]);

}());
