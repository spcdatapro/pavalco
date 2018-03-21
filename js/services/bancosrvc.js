(function(){

    var bancosrvc = angular.module('cpm.bancosrvc', ['cpm.comunsrvc']);

    bancosrvc.factory('bancoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/banco.php';

        var bancoSrvc = {
            lstBancos: function(idempresa){
                return comunFact.doGET(urlBase + '/lstbcos/' + idempresa);
            },
            getBanco: function(idbanco){
                return comunFact.doGET(urlBase + '/getbco/' + idbanco);
            },
            getCorrelativoBco: function(idbanco){
                return comunFact.doGET(urlBase + '/getcorrelabco/' + idbanco);
            },
            checkTranExists: function(idbanco, tipotrans, numero){
                return comunFact.doGET(urlBase + '/chkexists/' + idbanco + '/' + tipotrans + '/' + numero);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            },
            rptEstadoCta: function(obj){
                return comunFact.doPOST(urlBase + '/rptestcta', obj);
            }
        };
        return bancoSrvc;
    }]);

}());
