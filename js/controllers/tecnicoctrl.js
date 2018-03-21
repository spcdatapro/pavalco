(function(){

    var tecnicoctrl = angular.module('cpm.tecnicoctrl', []);

    tecnicoctrl.controller('tecnicoCtrl', ['$scope', 'authSrvc', '$route', 'tecnicoSrvc', '$confirm', function($scope, authSrvc, $route, tecnicoSrvc, $confirm){

        $scope.tecnico = {descripcion: ''};
        $scope.tecnicos = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstTecnicos = function(){ tecnicoSrvc.lstTecnicos().then(function(d){ $scope.tecnicos = d; }); };

        $scope.getTecnico = function(idtecnico){
            tecnicoSrvc.getTecnico(+idtecnico).then(function(d){
                $scope.tecnico = d[0];
                goTop();
            })
        };

        $scope.resetTecnico = function(){
            $scope.tecnico = { nombre: '', direccion: '', celular: '', email: '' };
        };

        function prepObj(obj){
            obj.direccion = obj.direccion != null && obj.direccion != undefined ? obj.direccion : '';
            obj.celular = obj.celular != null && obj.celular != undefined ? obj.celular : '';
            obj.email = obj.email != null && obj.email != undefined ? obj.email : '';
            return obj;
        }

        $scope.addTecnico = function(obj){
            obj = prepObj(obj);
            tecnicoSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstTecnicos();
                $scope.getTecnico(d.lastid);
            });
        };

        $scope.updTecnico = function(obj){
            obj = prepObj(obj);
            tecnicoSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstTecnicos();
                $scope.getTecnico(obj.id);
            });
        };

        $scope.delTecnico = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar al técnico ' + obj.nombre + '?',
                title: 'Eliminar técnico', ok: 'Sí', cancel: 'No'}).then(function() {
                tecnicoSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstTecnicos(); $scope.resetTecnico(); });
            });
        };

        $scope.getLstTecnicos();
    }]);

}());
