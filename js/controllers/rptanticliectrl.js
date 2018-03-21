(function(){

    var rptanticlictrl = angular.module('cpm.rptanticlictrl', []);

    rptanticlictrl.controller('rptAntiClientesCtrl', ['$scope', 'rptAntiClientesSrvc', 'authSrvc', 'jsReportSrvc', '$sce','clienteSrvc', function($scope, rptAntiClientesSrvc, authSrvc, jsReportSrvc, $sce, clienteSrvc){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), idempresa: 0,detalle: 0, cliente: {id: 0}};
        $scope.anticliente = [];
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
            $scope.anticliente = [];
        };

        $scope.getAntiCli = function(){
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.clistr = $scope.params.cliente.id;

            if($scope.params.detalle == 1){
                jsReportSrvc.antiClientesDet($scope.params).then(function (result) {
                    var file = new Blob([result.data], {type: 'application/pdf'});
                    var fileURL = URL.createObjectURL(file);
                    $scope.content = $sce.trustAsResourceUrl(fileURL);
                });
            }else {
                jsReportSrvc.antiClientes($scope.params).then(function (result) {
                    var file = new Blob([result.data], {type: 'application/pdf'});
                    var fileURL = URL.createObjectURL(file);
                    $scope.content = $sce.trustAsResourceUrl(fileURL);
                });
            }
        };

        $scope.getAntiCliXLSX = function(){
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.clistr = $scope.params.cliente.id;

            if($scope.params.detalle == 1){
                jsReportSrvc.antiClientesDetXlsx($scope.params).then(function (result) {
                    var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                    saveAs(file, 'AntiClientes.xlsx');
                });
            }else {
                jsReportSrvc.antiClientesXlsx($scope.params).then(function (result) {
                    var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                    saveAs(file, 'AntiClientes.xlsx');
                });
            }
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Antiguedad de CLientes');
        };

    }]);

}());

