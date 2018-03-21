(function(){

    var partesrvc = angular.module('cpm.partesrvc', ['cpm.comunsrvc']);

    partesrvc.factory('parteSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/parte.php';

        return {
            lstPartes: function(){
                return comunFact.doGET(urlBase + '/lstpartes');
            },
            getParte: function(idparte){
                return comunFact.doGET(urlBase + '/getparte/' + idparte);
            },
            lstImagenesParte: function(idparte){
                return comunFact.doGET(urlBase + '/lstimgparte/' + idparte);
            },
            getImagenParte: function(idparte){
                return comunFact.doGET(urlBase + '/getimgparte/' + idparte);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());
