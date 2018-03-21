(function(){

    var ubicacionctrl = angular.module('cpm.ubicacionctrl', []);

    ubicacionctrl.controller('ubicacionCtrl', ['$scope', 'authSrvc', '$route', 'ubicacionSrvc', '$confirm', function($scope, authSrvc, $route, ubicacionSrvc, $confirm){

        $scope.ubicacion = {descripcion: ''};
        $scope.ubicaciones = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstUbicaciones = function(){ ubicacionSrvc.lstUbicaciones().then(function(d){ $scope.ubicaciones = d; }); };

        $scope.getUbicacion = function(idubicacion){
            ubicacionSrvc.getUbicacion(+idubicacion).then(function(d){
                $scope.ubicacion = d[0];
                goTop();
            })
        };

        $scope.resetUbicacion = function(){
            $scope.ubicacion = { descripcion: '', direccion: '', telefono: '', contacto: '', debaja: '0' };
        };

        function prepObj(obj){
            obj.telefono = obj.telefono != null && obj.telefono != undefined ? obj.telefono : '';
            obj.contacto = obj.contacto != null && obj.contacto != undefined ? obj.contacto : '';
            obj.debaja = obj.debaja != null && obj.debaja != undefined ? obj.debaja : '0';
            return obj;
        }

        $scope.addUbicacion = function(obj){
            obj = prepObj(obj);
            ubicacionSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstUbicaciones();
                $scope.getUbicacion(d.lastid);
            });
        };

        $scope.updUbicacion = function(obj){
            obj = prepObj(obj);
            ubicacionSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstUbicaciones();
                $scope.getUbicacion(obj.id);
            });
        };

        $scope.delUbicacion = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar la Ubicación ' + obj.descripcion + '?',
                title: 'Eliminar ubicación', ok: 'Sí', cancel: 'No'}).then(function() {
                ubicacionSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstUbicaciones(); $scope.resetUbicacion(); });
            });
        };

        $scope.getLstUbicaciones();
    }]);

}());
