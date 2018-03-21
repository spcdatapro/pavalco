(function(){

    var rptconttecfuentectrl = angular.module('cpm.rptconttecfuentectrl', []);

    rptconttecfuentectrl.controller('rptConteoTecnicoFuenteCtrl', ['$scope', 'jsReportSrvc', '$sce', 'rptConteoTecnicoFuenteSrvc', '$window', function($scope, jsReportSrvc, $sce, rptConteoTecnicoFuenteSrvc, $window){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate()};
        //$scope.params = {del: moment('2016-01-01').toDate(), al: moment('2016-12-31').toDate()};
        $scope.conteos = [];

        $scope.getRepHtml = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            rptConteoTecnicoFuenteSrvc.rptConteoTecnicoFuente($scope.params).then(function(d){
                $scope.conteos = d;
                //console.log($scope.conteos);
            });
        };

        $scope.getRepPdf = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            jsReportSrvc.conteoFuenteTecnico($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $window.open($sce.trustAsResourceUrl(fileURL));
            });
        };

    }]);
}());
