(function(){

    var rptpdcasosfuentesrvc = angular.module('cpm.rptpdcasosfuentesrvc', ['cpm.comunsrvc']);

    rptpdcasosfuentesrvc.factory('rptPDCasosFuenteSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/rptpromdiacasosfuente.php';

        return {
            rptPDCasosFuente: function(obj){
                return comunFact.doPOST(urlBase + '/pdcasosfuente', obj);
            }
        };

    }]);

}());