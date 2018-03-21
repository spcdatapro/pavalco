(function(){

    var sectorctrl = angular.module('cpm.sectorctrl', []);

    sectorctrl.controller('sectorCtrl', ['$scope', 'sectorSrvc', '$confirm', function($scope, sectorSrvc, $confirm){

        $scope.sector = {descsector: ''};
        $scope.sectores = [];

        $scope.getLstSectores = function(){
            sectorSrvc.lstSectores().then(function(d){
                for(var i = 0; i < d.length; i++){ d[i].id = parseInt(d[i].id); }
                $scope.sectores = d;
            });
        };

        $scope.addSector = function(obj){
            sectorSrvc.editRow(obj, 'c').then(function(){
                $scope.getLstSectores();
                $scope.sector = {descsector: ''};
            });
        };

        $scope.updSector = function(data, id){
            data.id = id;
            sectorSrvc.editRow(data, 'u').then(function(){
                $scope.getLstSectores();
            });
        };

        $scope.delSector = function(id){
            $confirm({text: '¿Seguro(a) de eliminar este sector?', title: 'Eliminar sector', ok: 'Sí', cancel: 'No'}).then(function() {
                monedaSrvc.editRow({id: id}, 'd').then(function(){ $scope.getLstSectores(); });
            });
        };

        $scope.getLstSectores();
    }]);

}());
