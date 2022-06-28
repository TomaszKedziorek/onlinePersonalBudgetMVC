$("button.edit").on("click", function () {
  const editButtonID = $(this).attr('id');
  $("input[id=" + editButtonID + "]").prop('disabled', function (i, v) { return !v; });
  $("button.save[id=" + editButtonID + "]").prop('disabled', function (i, v) { return !v; });
  $("button.delete[id=" + editButtonID + "]").prop('disabled', function (i, v) { return !v; });
});

$("button.deleteModal").on("click", function () {
  const deleteButtonID = $(this).attr('id');
  $("input[id=delete-" + deleteButtonID + "][name=processedCategoryDelete]").prop('disabled', false).prop('checked', true);
});