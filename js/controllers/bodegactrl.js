(function(){

    var bodegactrl = angular.module('cpm.bodegactrl', []);

    bodegactrl.controller('bodegaCtrl', ['$scope', 'authSrvc', '$route', 'bodegaSrvc', '$confirm', function($scope, authSrvc, $route, bodegaSrvc, $confirm){

        $scope.bodega = {nombre: ''};
        $scope.bodegas = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstBodegas = function(){ bodegaSrvc.lstBodegas().then(function(d){ $scope.bodegas = d; }); };

        $scope.getBodega = function(idbodega){
            bodegaSrvc.getBodega(+idbodega).then(function(d){
                $scope.bodega = d[0];
                goTop();
            })
        };

        $scope.resetBodega = function(){
            $scope.bodega = { nombre: '', direccion: '', telefono: '', contacto: '' };
        };

        function prepObj(obj){
            obj.direccion = obj.direccion != null && obj.direccion != undefined ? obj.direccion : '';
            obj.telefono = obj.telefono != null && obj.telefono != undefined ? obj.telefono : '';
            obj.contacto = obj.contacto != null && obj.contacto != undefined ? obj.contacto : '';            
            return obj;
        }

        $scope.addBodega = function(obj){
            obj = prepObj(obj);
            bodegaSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstBodegas();
                $scope.getBodega(d.lastid);
            });
        };

        $scope.updBodega = function(obj){
            obj = prepObj(obj);
            bodegaSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstBodegas();
                $scope.getBodega(obj.id);
            });
        };

        $scope.delBodega = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar la bodega ' + obj.nombre + '?',
                title: 'Eliminar bodega', ok: 'Sí', cancel: 'No'}).then(function() {
                bodegaSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstBodegas(); $scope.resetBodega(); });
            });
        };

        $scope.getLstBodegas();
    }]);

}());
