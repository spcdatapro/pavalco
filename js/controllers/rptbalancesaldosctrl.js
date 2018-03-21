(function(){

    var rptbalsalctrl = angular.module('cpm.rptbalsalctrl', []);

    rptbalsalctrl.controller('rptBalanceSaldosCtrl', ['$scope', 'rptBalanceSaldosSrvc', 'empresaSrvc', 'authSrvc', 'jsReportSrvc', '$sce', function($scope, rptBalanceSaldosSrvc, empresaSrvc, authSrvc, jsReportSrvc, $sce){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), idempresa: 0, solomov: 1};
        $scope.balanceSaldos = [];
        $scope.content = undefined;

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                $scope.params.idempresa = parseInt(usrLogged.workingon);
            }
        });

        $scope.getBalSal = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.solomov = $scope.params.solomov != null && $scope.params.solomov != undefined ? $scope.params.solomov : 0;

            jsReportSrvc.balanceDeSaldos($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $scope.content = $sce.trustAsResourceUrl(fileURL);
            });
        };

        $scope.getBalSalXLSX = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.solomov = $scope.params.solomov != null && $scope.params.solomov != undefined ? $scope.params.solomov : 0;

            jsReportSrvc.balanceDeSaldosXlsx($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                saveAs(file, 'BalanceDeSaldos.xlsx');
            });
        };

        $scope.printVersion = function(){
            PrintElem('#toPrint', 'Balance de saldos');
        };

    }]);

}());
