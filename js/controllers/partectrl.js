(function(){

    var partectrl = angular.module('cpm.partectrl', []);

    partectrl.controller('parteCtrl', ['$scope', 'authSrvc', '$route', 'parteSrvc', '$confirm', 'proveedorSrvc', 'marcaSrvc', 'grupoParteSrvc', function($scope, authSrvc, $route, parteSrvc, $confirm, proveedorSrvc, marcaSrvc, grupoParteSrvc){

        $scope.parte = { codigo: '', codigointerno: '', idproveedor: undefined, idmarca: undefined, descripcion: '', idsubgrupo: undefined, minimo: 0, maximo: 0 };
        $scope.partes = [];
        $scope.imagen = {};
        $scope.imagenes = [];
        $scope.permiso = {};
        $scope.proveedores = [];
        $scope.marcas = [];
        $scope.subgrupos = [];

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        proveedorSrvc.lstProveedores().then(function(d){ $scope.proveedores = d; });
        marcaSrvc.lstMarcas().then(function(d){ $scope.marcas = d; });
        grupoParteSrvc.lstAllSubGruposPartes().then(function(d){ $scope.subgrupos = d; });

        function setPartes(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].minimo = parseInt(d[i].minimo);
                d[i].maximo = parseInt(d[i].maximo);
            }
            return d;
        }

        $scope.getLstPartes = function(){ parteSrvc.lstPartes().then(function(d){ $scope.partes = setPartes(d); }); };

        $scope.getParte = function(idparte){
            parteSrvc.getParte(+idparte).then(function(d){
                $scope.parte = setPartes(d)[0];
                $scope.resetImagen();
                $scope.getLstImagenes(idparte);
                goTop();
            })
        };

        $scope.resetParte = function(){
            $scope.parte = { codigo: '', codigointerno: '', idproveedor: undefined, idmarca: undefined, descripcion: '', idsubgrupo: undefined, minimo: 0, maximo: 0 };
            $scope.imagen = {};
            $scope.imagenes = [];
        };

        function prepObjParte(obj){
            obj.codigointerno = obj.codigointerno != null && obj.codigointerno != undefined ? obj.codigointerno : '';
            return obj;
        }

        $scope.addParte = function(obj){
            obj = prepObjParte(obj);
            parteSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstPartes();
                $scope.getParte(d.lastid);
            });
        };

        $scope.updParte = function(obj){
            obj = prepObjParte(obj);
            parteSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstPartes();
                $scope.getParte(obj.id);
            });
        };

        $scope.delParte = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar la parte ' + obj.codigo + '? (Esto también eliminará las imágenes asociadas a esta parte)',
                title: 'Eliminar parte', ok: 'Sí', cancel: 'No'}).then(function() {
                parteSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstPartes(); $scope.resetParte(); });
            });
        };

        $scope.getLstImagenes = function(idparte){
            parteSrvc.lstImagenesParte(+idparte).then(function(d){ $scope.imagenes = d; });
        };

        $scope.getImagen = function(idimagen){
            parteSrvc.getImagenParte(+idimagen).then(function(d){
                $scope.imagen = d[0];
                goTop();
            });
        };

        $scope.resetImagen = function(){
            $scope.imagen = { idparte: $scope.parte != null && $scope.parte != undefined ?($scope.parte.id > 0 ? $scope.parte.id : 0) : 0, descripcion: '', url: '' };
        };

        $scope.addImagen = function(obj){
            parteSrvc.editRow(obj, 'cd').then(function(d){
                $scope.getLstImagenes(obj.idparte);
                $scope.getImagen(d.lastid);
            });
        };

        $scope.updImagen = function(obj){
            parteSrvc.editRow(obj, 'ud').then(function(d){
                $scope.getLstImagenes(obj.idparte);
                $scope.getImagen(obj.id);
            });
        };

        $scope.delImagen = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar la imagen ' + obj.descripcion + '?',
                title: 'Eliminar imagen ', ok: 'Sí', cancel: 'No'}).then(function() {
                parteSrvc.editRow({id: obj.id}, 'dd').then(function(){ $scope.getLstImagenes(obj.idparte); $scope.resetImagen(); });
            });
        };

        $scope.getLstPartes();
    }]);

}());