(function(){

    var sectorsrvc = angular.module('cpm.sectorsrvc', ['cpm.comunsrvc']);

    sectorsrvc.factory('sectorSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/sector.php';

        var sectorSrvc = {
            lstSectores: function(){
                return comunFact.doGET(urlBase + '/lstsectores');
            },
            getSector: function(idsector){
                return comunFact.doGET(urlBase + '/getsector/' + idsector);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return sectorSrvc;
    }]);

}());

