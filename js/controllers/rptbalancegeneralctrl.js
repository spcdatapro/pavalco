(function(){

    var rptbalgenctrl = angular.module('cpm.rptbalgenctrl', []);

    rptbalgenctrl.controller('rptBalanceGeneralCtrl', ['$scope', 'rptBalanceGeneralSrvc', 'empresaSrvc', 'authSrvc', 'jsReportSrvc', '$sce', function($scope, rptBalanceGeneralSrvc, empresaSrvc, authSrvc, jsReportSrvc, $sce){

        $scope.params = {al: moment().endOf('month').toDate(), idempresa: 0, acumulado: 1, nivel: '6', solomov: 1};
        $scope.balancegeneral = [];

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                $scope.params.idempresa = parseInt(usrLogged.workingon);
            }
        });

        $scope.getBalGen = function(){
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.acumulado = 1;
            $scope.params.solomov = $scope.params.solomov != null && $scope.params.solomov != undefined ? $scope.params.solomov : 0;
            //console.log($scope.params); return;
            jsReportSrvc.balanceGeneral($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $scope.content = $sce.trustAsResourceUrl(fileURL);
            });
        };

        $scope.getBalGenXlsx = function(){
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.acumulado = 1;
            $scope.params.solomov = $scope.params.solomov != null && $scope.params.solomov != undefined ? $scope.params.solomov : 0;
            jsReportSrvc.balanceGeneralXlsx($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                saveAs(file, 'BalanceGeneral.xlsx');
            });
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Estado de resultados');
        };

    }]);

}());
