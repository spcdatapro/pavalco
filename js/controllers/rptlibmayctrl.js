(function(){

    var rptlibmayctrl = angular.module('cpm.rptlibmayctrl', []);

    rptlibmayctrl.controller('rptLibroMayorCtrl', ['$scope', 'rptLibroMayorSrvc', 'empresaSrvc', 'authSrvc', 'jsReportSrvc', '$sce', function($scope, rptLibroMayorSrvc, empresaSrvc, authSrvc, jsReportSrvc, $sce){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), idempresa: 0, codigo: ''};
        $scope.libromayor = [];

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                $scope.params.idempresa = parseInt(usrLogged.workingon);
            }
        });

        $scope.getLibroMayor = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.codigo = $scope.params.codigo != null && $scope.params.codigo != undefined ? $scope.params.codigo : '';

            jsReportSrvc.libroMayor($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $scope.content = $sce.trustAsResourceUrl(fileURL);
            });

        };

        $scope.getLibroMayorXLSX = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.codigo = $scope.params.codigo != null && $scope.params.codigo != undefined ? $scope.params.codigo : '';

            jsReportSrvc.libroMayorXlsx($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                saveAs(file, 'LibroMayor.xlsx');
            });

        };

        //$scope.printVersion = function(){ PrintElem('#toPrint', 'Libro Mayor'); };

    }]);

}());