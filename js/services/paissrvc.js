(function(){

    var paissrvc = angular.module('cpm.paissrvc', ['cpm.comunsrvc']);

    paissrvc.factory('paisSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/pais.php';

        var paisSrvc = {
            lstPaises: function(){
                return comunFact.doGET(urlBase + '/lstpaises');
            },
            getPais: function(idpais){
                return comunFact.doGET(urlBase + '/getpais/' + idpais);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return paisSrvc;
    }]);

}());
