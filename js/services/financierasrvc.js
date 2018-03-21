(function(){

    var financierasrvc = angular.module('cpm.financierasrvc', ['cpm.comunsrvc']);

    financierasrvc.factory('financieraSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/financiera.php';

        var financieraSrvc = {
            lstAllFinancieras: function(){
                return comunFact.doGET(urlBase + '/lstallfinan');
            },
            lstFinancierasByTipo: function(idtipofinan){
                return comunFact.doGET(urlBase + '/lstfinanbytipo/' + idtipofinan);
            },
            getFinanciera: function(idfinan){
                return comunFact.doGET(urlBase + '/getfinan/' + idfinan);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return financieraSrvc;
    }]);

}());
