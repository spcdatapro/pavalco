(function(){

    var bodegasrvc = angular.module('cpm.bodegasrvc', ['cpm.comunsrvc']);

    bodegasrvc.factory('bodegaSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/bodega.php';

        return {
            lstBodegas: function(){
                return comunFact.doGET(urlBase + '/lstbodegas');
            },
            getBodega: function(idbodega){
                return comunFact.doGET(urlBase + '/getbodega/' + idbodega);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());

