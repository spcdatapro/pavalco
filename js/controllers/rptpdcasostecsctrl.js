(function(){

    var rptpdcasostecsctrl = angular.module('cpm.rptpdcasostecsctrl', []);

    rptpdcasostecsctrl.controller('rptPDCasosTecnicosCtrl', ['$scope', 'jsReportSrvc', '$sce', 'rptPDCasosTecnicosSrvc', '$window', function($scope, jsReportSrvc, $sce, rptPDCasosTecnicosSrvc, $window){

        $scope.params = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate()};
        $scope.promedios = { diashabiles: 0, promedios: [] };

        $scope.getRepHtml = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            rptPDCasosTecnicosSrvc.rptPDCasosTecnicos($scope.params).then(function(d){ $scope.promedios = d; });
        };

        $scope.getRepPdf = function(){
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            jsReportSrvc.promDiarioCasosTecnicos($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $window.open($sce.trustAsResourceUrl(fileURL));
            });
        };

    }]);
}());