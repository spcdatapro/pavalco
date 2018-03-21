(function(){

    var titulosrvc = angular.module('cpm.titulosrvc', ['cpm.comunsrvc']);

    titulosrvc.factory('tituloSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/titulo.php';

        var tituloSrvc = {
            lstTitulos: function(){
                return comunFact.doGET(urlBase + '/lsttitulos');
            },
            getTitulo: function(idtitulo){
                return comunFact.doGET(urlBase + '/gettitulo/' + idtitulo);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return tituloSrvc;
    }]);

}());