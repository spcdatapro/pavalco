(function(){

    var rptestresctrl = angular.module('cpm.rptestresctrl', []);

    rptestresctrl.controller('rptEstadoResultadosCtrl', ['$scope', 'rptEstadoResultadosSrvc', 'empresaSrvc', 'authSrvc', 'jsReportSrvc', '$sce', function($scope, rptEstadoResultadosSrvc, empresaSrvc, authSrvc, jsReportSrvc, $sce){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), idempresa: 0, acumulado: 0, nivel: '6'};
        $scope.estadoresultados = [];

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                $scope.params.idempresa = parseInt(usrLogged.workingon);
            }
        });

        function procDataEstRes(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].idcuenta = parseInt(d[i].idcuenta);
                d[i].tipocuenta = parseInt(d[i].tipocuenta);
                d[i].ingresos = parseInt(d[i].ingresos);
                d[i].parasuma = parseInt(d[i].parasuma);
                d[i].estotal = parseInt(d[i].estotal);
                d[i].saldo = parseFloat(parseFloat(d[i].saldo).toFixed(2));
            }
            return d;
        }

        $scope.getEstRes = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.acumulado = $scope.params.acumulado != null && $scope.params.acumulado != undefined ? $scope.params.acumulado : 0;
            //console.log($scope.params); return;
            jsReportSrvc.estadoResultados($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $scope.content = $sce.trustAsResourceUrl(fileURL);
            });
            //rptEstadoResultadosSrvc.rptEstRes($scope.params).then(function(d){ $scope.estadoresultados = procDataEstRes(d); });
        };

        $scope.getEstResXLSX = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.acumulado = $scope.params.acumulado != null && $scope.params.acumulado != undefined ? $scope.params.acumulado : 0;
            jsReportSrvc.estadoResultadosXlsx($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                saveAs(file, 'EstadoDeResultados.xlsx');
            });
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Estado de resultados');
        };

    }]);

}());
