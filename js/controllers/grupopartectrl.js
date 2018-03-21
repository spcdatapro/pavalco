(function(){

    var grupopartectrl = angular.module('cpm.grupopartectrl', []);

    grupopartectrl.controller('grupoParteCtrl', ['$scope', 'authSrvc', '$route', 'grupoParteSrvc', '$confirm', function($scope, authSrvc, $route, grupoParteSrvc, $confirm){

        $scope.grupo = {descripcion: ''};
        $scope.grupos = [];
        $scope.subgrupo = {};
        $scope.subgrupos = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstGrupos = function(){ grupoParteSrvc.lstGruposPartes().then(function(d){ $scope.grupos = d; }); };

        $scope.getGrupo = function(idgrupoparte){
            grupoParteSrvc.getGrupoParte(+idgrupoparte).then(function(d){
                $scope.grupo = d[0];
                $scope.resetSubGrupo();
                $scope.getLstSubGrupos(idgrupoparte);
                goTop();
            })
        };

        $scope.resetGrupo = function(){
            $scope.grupo = {descripcion: ''};
            $scope.subgrupo = {};
            $scope.subgrupos = [];
        };

        $scope.addGrupo = function(obj){
            grupoParteSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstGrupos();
                $scope.getGrupo(d.lastid);
            });
        };

        $scope.updGrupo = function(obj){
            grupoParteSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstGrupos();
                $scope.getGrupo(obj.id);
            });
        };

        $scope.delGrupo = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar el grupo ' + obj.descripcion + '? (Esto también eliminará los subgrupos asociados a este grupo)',
                title: 'Eliminar grupo', ok: 'Sí', cancel: 'No'}).then(function() {
                grupoParteSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstGrupos(); $scope.resetGrupo(); });
            });
        };

        $scope.getLstSubGrupos = function(idgrupoparte){
            grupoParteSrvc.lstSubGruposPartesByGrupo(+idgrupoparte).then(function(d){
                $scope.subgrupos = d;
            });
        };

        $scope.getSubGrupo = function(idsubgrupo){
            grupoParteSrvc.getSubGrupoParte(+idsubgrupo).then(function(d){
                $scope.subgrupo = d[0];
                goTop();
            });
        };

        $scope.resetSubGrupo = function(){
            $scope.subgrupo = {
                idgrupoparte: $scope.grupo != null && $scope.grupo != undefined ?($scope.grupo.id > 0 ? $scope.grupo.id : 0) : 0,
                descripcion: ''
            };
        };

        $scope.addSubGrupo = function(obj){
            grupoParteSrvc.editRow(obj, 'cd').then(function(d){
                $scope.getLstSubGrupos(obj.idgrupoparte);
                $scope.getSubGrupo(d.lastid);
            });
        };

        $scope.updSubGrupo = function(obj){
            grupoParteSrvc.editRow(obj, 'ud').then(function(d){
                $scope.getLstSubGrupos(obj.idgrupoparte);
                $scope.getSubGrupo(obj.id);
            });
        };

        $scope.delSubGrupo = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar ' + obj.grupoparte + ' - ' + obj.descripcion + '?',
                title: 'Eliminar subgrupo de parte', ok: 'Sí', cancel: 'No'}).then(function() {
                grupoParteSrvc.editRow({id: obj.id}, 'dd').then(function(){ $scope.getLstSubGrupos(obj.idgrupoparte); $scope.resetSubGrupo(); });
            });
        };

        $scope.getLstGrupos();
    }]);

}());