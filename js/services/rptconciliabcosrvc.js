(function(){

    var rptconciliabcosrvc = angular.module('cpm.rptconciliabcosrvc', ['cpm.comunsrvc']);

    rptconciliabcosrvc.factory('rptConciliaBcoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/rptconciliabco.php';

        return {
            rptConciliaBco: function(obj){
                return comunFact.doPOST(urlBase + '/rptconciliabco', obj);
            },
            historial: function(idempresa){
                return comunFact.doGET(urlBase + '/histocon/' + idempresa);
            },
            getcon: function(idhisto){
                return comunFact.doGET(urlBase + '/getcon/' + idhisto);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());
