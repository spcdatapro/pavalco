(function(){

    var rptintegraanualsrvc = angular.module('cpm.rptintegraanualsrvc', ['cpm.comunsrvc']);

    rptintegraanualsrvc.factory('rptIntegracionAnualSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/rptintegracion.php';

        return {
            rptIntegracionAnual: function(obj){
                return comunFact.doPOST(urlBase + '/rptintegra', obj);
            }
        };

    }]);

}());
