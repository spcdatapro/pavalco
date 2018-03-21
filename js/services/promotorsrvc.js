(function(){

    var promotorsrvc = angular.module('cpm.promotorsrvc', ['cpm.comunsrvc']);

    promotorsrvc.factory('promotorSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/promotor.php';

        var promotorSrvc = {
            lstPromotores: function(){
                return comunFact.doGET(urlBase + '/lstpromotores');
            },
            getPromotor: function(idpromtor){
                return comunFact.doGET(urlBase + '/getpromotor/' + idpromtor);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return promotorSrvc;
    }]);

}());
