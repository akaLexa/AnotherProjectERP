
$(document).ready(function () {
    projectFilter();
});

function projectFilter() {
    genIn({
        element:'projectListContent',
        address:'|site|page/|currentPage|/GetProjects',
        loadicon:'<tr><td colspan="7" style="text-align: center; color: gray;">Загружаю..</td></tr>'
    });
}