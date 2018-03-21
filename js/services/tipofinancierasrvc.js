(function(){

    var tipofinancierasrvc = angular.module('cpm.tipofinancierasrvc', ['cpm.comunsrvc']);

    tipofinancierasrvc.factory('tipoFinancieraSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/tipofinanciera.php';

        var tipoFinancieraSrvc = {
            lstTiposFinanciera: function(){
                return comunFact.doGET(urlBase + '/lsttiposfinan');
            },
            getTipoFinanciera: function(idtipofinan){
                return comunFact.doGET(urlBase + '/gettipofinan/' + idtipofinan);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return tipoFinancieraSrvc;
    }]);

}());
