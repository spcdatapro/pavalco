(function(){

    var rptpdcasostecssrvc = angular.module('cpm.rptpdcasostecssrvc', ['cpm.comunsrvc']);

    rptpdcasostecssrvc.factory('rptPDCasosTecnicosSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/rptpdcasostecnicos.php';

        return {
            rptPDCasosTecnicos: function(obj){
                return comunFact.doPOST(urlBase + '/pdcasostecs', obj);
            }
        };

    }]);

}());