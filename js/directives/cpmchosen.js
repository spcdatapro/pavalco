(function(){

    var cpmchosen = angular.module('cpm.cpmchosen', []);

    cpmchosen.directive('chosen', ['$timeout', function($timeout){
        var linker = function(scope, element, attr) {
            $timeout(function () {
                element.chosen();
            }, 0, false);
        };
        return {
            restrict: 'A',
            link: linker
        };
    }]);

}());

