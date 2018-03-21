(function(){

    var equipoctrl = angular.module('cpm.equipoctrl', []);

    equipoctrl.controller('equipoCtrl', ['$scope', 'authSrvc', '$route', 'equipoSrvc', '$confirm', function($scope, authSrvc, $route, equipoSrvc, $confirm){

        $scope.equipo = {descripcion: ''};
        $scope.equipos = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstEquipos = function(){ equipoSrvc.lstEquipos().then(function(d){ $scope.equipos = d; }); };

        $scope.getEquipo = function(idequipo){
            equipoSrvc.getEquipo(+idequipo).then(function(d){
                $scope.equipo = d[0];
                goTop();
            })
        };

        $scope.resetEquipo = function(){
            $scope.equipo = {descripcion: ''};
        };

        $scope.addEquipo = function(obj){
            equipoSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstEquipos();
                $scope.getEquipo(d.lastid);
            });
        };

        $scope.updEquipo = function(obj){
            equipoSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstEquipos();
                $scope.getEquipo(obj.id);
            });
        };

        $scope.delEquipo = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar el equipo ' + obj.descripcion + '?',
                title: 'Eliminar equipo', ok: 'Sí', cancel: 'No'}).then(function() {
                equipoSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstEquipos(); $scope.resetEquipo(); });
            });
        };

        $scope.getLstEquipos();
    }]);

}());