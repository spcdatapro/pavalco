(function(){

    var razoncambiosrvc = angular.module('cpm.razoncambiosrvc', ['cpm.comunsrvc']);

    razoncambiosrvc.factory('razonCambioSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/razoncambio.php';

        return {
            lstRazonesCambio: function(){
                return comunFact.doGET(urlBase + '/lstrazonescambio');
            },
            getRazonCambio: function(idrazoncambio){
                return comunFact.doGET(urlBase + '/getrazoncambio/' + idrazoncambio);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());