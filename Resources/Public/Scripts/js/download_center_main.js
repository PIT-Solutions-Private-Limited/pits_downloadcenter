var app = angular.module("filterApp", ['angularUtils.directives.dirPagination']);
var flag = 0; 
app.controller("filterCtrl", function($scope, $http, $location) {

	var actionURL = document.getElementById("actionURL").value;
	
	$http.get(actionURL).success(function(data){
		//  url configurations
		document.getElementById('loader').style.display = 'none';
		$scope.orderByField = 'title';
  		$scope.reverseSort = false;
		$scope.locations = $location.search();

		var filterObj = {};
		if($scope.locations.filter!=undefined){
			var extractVal = $scope.locations.filter.split(',');
			angular.forEach(extractVal,function(key,val){
				filterObj[key] = true;
			});
		}
		if($scope.locations.keyword!=undefined){
			$scope.search = $scope.locations.keyword;
		}

		if($scope.locations.category!=undefined){
			$scope.dummyCategory = $scope.locations.category;
		}
		

		$scope.items = data;
		$scope.currentPage = 1;
        $scope.pageSize = ($scope.items.config.paginationcount > 0)?$scope.items.config.paginationcount:10;
		$scope.checkFilter = filterObj;
		$scope.newCat = $scope.items.categories;
		$scope.checkCategory =[{val:$scope.items.categories}];
		$scope.dummyCategory ={};

		$scope.newArray = function(obj){
			$scope.checkCategory.splice(obj.index+1);
			$scope.checkCategory.push({id:obj.id,val:obj.val,index:obj.index});
		}

		$scope.removeArray = function(index){
			$scope.checkCategory.splice(index+1);
		}

		$scope.newVal = "";
		$scope.predicate;
		$scope.base = data.baseURL;

		$scope.byTypes = function(data){
			return $scope.checkFilter[data.dataType] || reset($scope.checkFilter)
		}
		
		$scope.changeCat = function(id){
			$scope.dummyCategory = id;
		}
		$scope.$watch('checkCategory',function(o,n){
			$scope.categories = $scope.checkCategory;
		},true);

		$scope.$watch('dummyCategory.categories',function(o,n){
			$scope.currentPage = 1;	
			if(o){
				$location.search('category', o[0]);
			}else{
				$location.search('category', null);
			}
		},true);

		$scope.$watch('search',function(o,n){
			$scope.currentPage = 1;	
			if(o!=""){
				$location.search('keyword', o);
			}else{
				$location.search('keyword', null);
			}
		},true);

		$scope.$watch('checkFilter',function(o,n){
			$scope.currentPage = 1;
			if(o!=undefined){
				var filterUrl =[];
				angular.forEach($scope.checkFilter,function(a,b){
					if(a){
						filterUrl.push(b)
					}
				});
				if(filterUrl.length>0){
					$location.search('filter', filterUrl.joinactionURL(","));
				}else{
					$location.search('filter', null);
				}
			}
		},true);
		$scope.resetDropDown = function(elem) {	
			flag = 1;
			elem = elem.target
			$(elem).prev().val("");
			$(elem).prev().parent('custom-select').nextAll('custom-select').remove();
			if($(elem).prev().parent('custom-select').prev('custom-select').length){
				var afterReset = $(elem).parent('custom-select').prev('custom-select').children('select').find('option:selected').attr('data-resetClear');
				afterReset = JSON.parse(afterReset);
				$scope.changeCat(afterReset);
			}
			else{
				$scope.changeCat("");
			}
	    }
	});
});

app.filter('offset', function() {
	return function(input, start) {
		if(input){
			start = parseInt(start, 10);
			return input.slice(start);
		}
	};
});

app.directive('customSelect',function(){
	return {
		restrict:"E",
		scope:true,
		link:function(scope,element,attr){
			scope.dummyCategory = {};
		},
		template: '<select class="form-control cat-select" subcat ng-model="dummyCategory.categories" ng-options="item.id as item.title for item in cat.val"><option value="">{{items.transilations.categoryplaceholder}}</option></select><span class="input-group-addon filter43-cats" ng-click="resetDropDown($event)"></span>'
	}
});

app.directive("subcat", function($compile, $timeout){
	return {
		restrict:"A",
		scope:true,
		link:function(scope,element,attr){
			element.bind("change",function($index){
				$timeout(function(){
					flag = 0;
					scope.dummyCategory.categories = [scope.dummyCategory.categories];		
					var newValue = scope.cat.val.filter(function(item)	{
						return item.id == scope.dummyCategory.categories
					});
					if(newValue[0] == undefined){
						$(element).siblings('span').click();
						return false;
					}
					if(newValue[0]){
						itemTitle = newValue[0].title;
					}
					if(newValue[0] && 'input' in newValue[0]){												
						getVal(scope, newValue[0]);
						scope.newArray({categories:scope.dummyCategory,val:newValue[0].input,index:scope.$index});
						element[0].blur();						
					}
					else{
						scope.removeArray(scope.$index);
					}
					scope.changeCat(scope.dummyCategory);
				}, 100, true);	
			});			
		}
	}
});

app.config(function($locationProvider) {
	$locationProvider.html5Mode({
		enabled: true,
		requireBase: false
	});
});
function reset(filterObj) {
	for (var key in filterObj) {
		if (filterObj[key]) {
			return false;
		} 
	}
	return true;
}
app.filter('newFilter', function ($location) {	
	return function(items, selVal){
		if (selVal && selVal.categories) {
			var filtered = [];
			angular.forEach(items, function(item) {					
				angular.forEach(selVal.categories, function(selectedValue) {
					if(item.categories[0] == selectedValue){
						filtered.push(item);
					}
				});
			});
			if(flag != 1){
				$('.cat-select').each(function(){
					$(this).children('option').each(function(){
						if($(this).attr('label') == itemTitle){
							$(this).prop('selected', true);
							afterReset = JSON.stringify(selVal);
							$(this).attr('data-resetClear', afterReset);
						}
					});
				});
			}
         	return filtered;
        }
		else {
        	return items;
      	}
	}				
});
function getVal(scope, newValue){
	var testArray = {testval:newValue.input};	
	var tvLength = testArray.testval.length;
	for(var i=0; i<tvLength; i++){
		scope.dummyCategory.categories.push(testArray.testval[i].id);
		if(testArray.testval[i].input){
			getVal(scope, testArray.testval[i]);
		}	
	}
	return scope.dummyCategory.categories;
}