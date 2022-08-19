/* remember the current fieldtype */
var currentFieldType = '';
/* storage for fields */
var finalContentBlockConfig = {};
/* onload, get the input defaults */
var inputDefaults = [];
var inputFields = [];
/* onload, get the checkbox defaults */
var checkboxDefaults = [];
var checkboxFields = [];
/* onload, get the textarea defaults */
var textareaDefaults = [];
var textareaFields = [];
/* on load, remember default values */
document.addEventListener("DOMContentLoaded", function (event) {
  inputFields = Array.from(document.querySelectorAll('.new-block input[type=text]'));
  if (Array.isArray(inputFields)) {
    inputFields.forEach(function (item, index, arr) {
      if (item.value.length > 0) inputDefaults[item.id] = item.value;
    });
  }
  checkboxFields = Array.from(document.querySelectorAll('.new-block input[type=checkbox]'));
  if (Array.isArray(checkboxFields)) {
    checkboxFields.forEach(function (item, index, arr) {
      if (item.checked) checkboxDefaults[item.id] = true;
    });
  }
  textareaFields = Array.from(document.querySelectorAll('.new-block textarea'));
  if (Array.isArray(textareaFields)) {
    textareaFields.forEach(function (item, index, arr) {
      if (item.value.length > 0) textareaDefaults[item.id] = item.value;
    });
  }
});

/* init next step */
function nextStep(numberNext) {
  /* validation */
  if (!validation(numberNext)) return;

  if (numberNext == 5) document.querySelectorAll('.step-' + numberNext)[0].classList.toggle('open');
  else document.querySelectorAll('.step-' + numberNext)[0].classList.add('open');

  if (numberNext == 2) document.querySelectorAll('.field-list')[0].classList.add('open');
}

/* init chosen field type */
function setupField(fieldType) {
  document.querySelectorAll('.step-4')[0].classList.add(fieldType);
  document.querySelectorAll('.step-2 button.' + fieldType)[0].classList.add('btn-green');
  currentFieldType = fieldType;
}

/* validate, if next step could be initiated */
function validation(numberNext) {
  /* validation */
  if (document.getElementById('cb-package-name').value.length < 1 && numberNext === 2) {
    document.getElementById('cb-package-name').classList.add('blob-error');
    return false;
  } else document.getElementById('cb-package-name').classList.remove('blob-error');

  if (document.getElementById('cb-field-identifier').value.length < 1 && numberNext === 4) {
    document.getElementById('cb-field-identifier').classList.add('blob-error');
    return false;
  } else document.getElementById('cb-field-identifier').classList.remove('blob-error');
  return true;
  /* end validation */
}

/* reset all fields to the default values */
function resetFields() {
  // remove all classes from the step-4 wraper
  var fieldTypes = ['Text', 'Textarea', 'Image', 'Url', 'Select', 'MultiSelect', 'Checkbox', 'Radiobox'];
  fieldTypes.forEach(function (item, index, arr) {
    document.querySelectorAll('.step-4')[0].classList.remove(item);
  });
  document.querySelectorAll('.step-4')[0].classList.remove('open');
  document.querySelectorAll('.step-3')[0].classList.remove('open');

  if (Array.isArray(inputFields)) {
    inputFields.forEach(function (item, index, arr) {
      (inputDefaults[item.id] ? item.value = inputDefaults[item.id] : item.value = "");
    });
  }

  if (Array.isArray(checkboxFields)) {
    checkboxFields.forEach(function (item, index, arr) {
      (checkboxDefaults[item.id] ? item.checked = "checked" : item.checked = false);
    });
  }

  if (Array.isArray(textareaFields)) {
    textareaFields.forEach(function (item, index, arr) {
      (textareaDefaults[item.id] ? item.value = textareaDefaults[item.id] : item.value = "");
    });
  }

  var fieldButtons = Array.from(document.querySelectorAll('.step-2 button.btn-green'));
  if (Array.isArray(fieldButtons)) {
    fieldButtons.forEach(function (button, index, arr) {
      button.classList.remove('btn-green');
    });
  }


}

/* write final configuration */
function saveField() {
  // only if it is possible to write any fields
  if (document.getElementById('cb-package-name').value.length < 1 || document.getElementById('cb-field-identifier').value.length < 1) {
    return;
  }
  var tempIdentifier = document.getElementById('cb-field-identifier').value;
  if (Array.isArray(finalContentBlockConfig [tempIdentifier])) {
    tempIdentifier = handleIdentifierConflicts(tempIdentifier, 0);
  }

  finalContentBlockConfig['packageName'] = document.getElementById('cb-package-name').value;
  finalContentBlockConfig['backendName'] = document.getElementById('cb-package-nameTranslation').value;
  // finalContentBlockConfig['group'] = 'common'; // TODO: make that configurable in the Wizard
  finalContentBlockConfig [tempIdentifier] = {};
  finalContentBlockConfig [tempIdentifier]['type'] = currentFieldType;
  finalContentBlockConfig [tempIdentifier]['properties'] = {};

  var elementInputPoperties = Array.from(document.querySelectorAll('.new-block input[type=text].' + currentFieldType));
  if (Array.isArray(elementInputPoperties)) {
    elementInputPoperties.forEach(function (item, index, arr) {
      var popertyName = item.id.replace("cb-field-", "");
      finalContentBlockConfig [tempIdentifier]['properties'][popertyName] = item.value;
    });
  }

  var elementCheckboxPoperties = Array.from(document.querySelectorAll('.new-block input[type=checkbox].' + currentFieldType));
  if (Array.isArray(elementCheckboxPoperties)) {
    elementCheckboxPoperties.forEach(function (item, index, arr) {
      var popertyName = item.id.replace("cb-field-", "");
      finalContentBlockConfig [tempIdentifier]['properties'][popertyName] = item.checked;
    });
  }

  var elementTextareaPoperties = Array.from(document.querySelectorAll('.new-block textarea.' + currentFieldType));
  if (Array.isArray(elementTextareaPoperties)) {
    elementTextareaPoperties.forEach(function (item, index, arr) {
      var popertyName = item.id.replace("cb-field-", "");
      finalContentBlockConfig [tempIdentifier]['properties'][popertyName] = item.value;
    });
  }

  var newfield = document.createElement('button');
  newfield.classList.add('btn-default');
  newfield.classList.add('btn');
  newfield.classList.add('btn-green');
  newfield.innerText = (document.getElementById('cb-field-translationLabel').value.length > 0 ? document.getElementById('cb-field-translationLabel').value : tempIdentifier) + ' (click to remove)';
  newfield.setAttribute('data-identifier', tempIdentifier);
  newfield.setAttribute('onclick', 'removeFieldFromList("listed-cb-field-' + tempIdentifier + '")');
  newfield.id = 'listed-cb-field-' + tempIdentifier;
  // newfield.addEventListener('click', removeFieldFromList(newfield));
  document.querySelectorAll('.field-list')[0].appendChild(newfield);

}

/* handle identifier conflicts */
function handleIdentifierConflicts(identifier, counter) {
  counter++;
  if (Array.isArray(finalContentBlockConfig [identifier + '-' + counter])) {
    return handleIdentifierConflicts(identifier, counter);
  } else return identifier + '-' + counter;
}

/* remove field */
function removeFieldFromList(field) {
  field = document.getElementById(field);
  finalContentBlockConfig[field.getAttribute('data-identifier')] = '';
  field.remove();
}

function submitData() {
  var formular = document.getElementById('contenBlockSubmit');
  formular.elements.contentBlocksDataField.value = JSON.stringify(finalContentBlockConfig);
  formular.submit();
}
