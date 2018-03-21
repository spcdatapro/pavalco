(function(){

    var marcactrl = angular.module('cpm.marcactrl', []);

    marcactrl.controller('marcaCtrl', ['$scope', 'authSrvc', '$route', 'marcaSrvc', '$confirm', function($scope, authSrvc, $route, marcaSrvc, $confirm){

        $scope.marca = {descripcion: ''};
        $scope.marcas = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstMarcas = function(){ marcaSrvc.lstMarcas().then(function(d){ $scope.marcas = d; }); };

        $scope.getMarca = function(idmarca){
            marcaSrvc.getMarca(+idmarca).then(function(d){
                $scope.marca = d[0];
                goTop();
            })
        };

        $scope.resetMarca = function(){
            $scope.marca = {descripcion: ''};
        };

        $scope.addMarca = function(obj){
            marcaSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstMarcas();
                $scope.getMarca(d.lastid);
            });
        };

        $scope.updMarca = function(obj){
            marcaSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstMarcas();
                $scope.getMarca(obj.id);
            });
        };

        $scope.delMarca = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar la marca ' + obj.descripcion + '?',
                title: 'Eliminar marca', ok: 'Sí', cancel: 'No'}).then(function() {
                marcaSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstMarcas(); $scope.resetMarca(); });
            });
        };

        $scope.getLstMarcas();
    }]);

}());