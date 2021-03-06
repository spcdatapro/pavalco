(function(){

    var rptlibdiactrl = angular.module('cpm.rptlibdiactrl', []);

    rptlibdiactrl.controller('rptLibroDiarioCtrl', ['$scope', 'empresaSrvc', 'authSrvc', 'jsReportSrvc', '$sce', function($scope, empresaSrvc, authSrvc, jsReportSrvc, $sce){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), idempresa: 0};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                $scope.params.idempresa = parseInt(usrLogged.workingon);
            }
        });

        $scope.getLibroDiario = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            jsReportSrvc.libroDiario($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $scope.content = $sce.trustAsResourceUrl(fileURL);
            });
        };

        $scope.getLibroDiarioXLSX = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            jsReportSrvc.libroDiarioXlsx($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                saveAs(file, 'LibroDeDiario.xlsx');
            });
        };
    }]);
}());
