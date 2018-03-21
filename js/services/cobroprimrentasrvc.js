(function(){

    var cobroprimrentasrvc = angular.module('cpm.cobroprimrentasrvc', ['cpm.comunsrvc']);

    cobroprimrentasrvc.factory('cobroPrimRentaSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/cobroprimrenta.php';

        var cobroPrimRentaSrvc = {
            lstCobroPrimRenta: function(){
                return comunFact.doGET(urlBase + '/lstcobroprimrenta');
            },
            getCobroPrimRenta: function(idcobroprimrenta){
                return comunFact.doGET(urlBase + '/getcobroprimrenta/' + idcobroprimrenta);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return cobroPrimRentaSrvc;
    }]);

}());
