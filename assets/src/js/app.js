import $ from 'jquery';

// Check if an object has a property with truthy value.
const hasTruthy = (object, prop) => (
  Object.prototype.hasOwnProperty.call(object, prop) && object[prop]
);

// Track form submissions.
const submissions = {};

// Handle member form ajax submissions.
const submitMemberForm = async (e) => {
  e.preventDefault();

  const form = $(e.target);

  // The form hook name.
  const hook = form.data('form-hook');

  // Check if the form has already been submitted.
  if (hasTruthy(submissions, hook)) {
    return;
  }

  submissions[hook] = true;

  const body = new FormData(form[0]);
  const action = form.attr('action') || window.location.pathname;
  const method = form.attr('method') || 'GET';
  const headers = new Headers({ 'Accept': 'application/json' });

  // Remove existing validation messages.
  form.find('.is-invalid').removeClass('is-invalid');
  form.find('.invalid-feedback, .error-for').remove();
  form.find('[type="submit"] .spinner').show();

  try {
    const response = await fetch(action, { method, body, headers });
    const json = await response.json();

    const { errors = {}, redirect = '' } = json;

    // If the form submission was successful, redirect to the next page.
    if (redirect) {
      window.location = redirect;

      return;
    }

    // Iterate through validation errors.
    if (Object.keys(errors).length) {
      Object.keys(errors).forEach((key) => {
        const parsedKey = key.replace(/([[]])/g, '\\$1');
        const input = form.find(`[name="${parsedKey}"]`);

        if (!input.length) {
          const errorFor = form.find(`[data-error-for="${parsedKey}"]`);

          if (errorFor.length) {
            errorFor.append(`<div class="invalid-feedback error-for">${errors[key]}</div>`);
          }

          return;
        }

        input.addClass('is-invalid');
        input.parent().append(`<small class="invalid-feedback">${errors[key]}</small>`);
      });
    }
  } catch (err) {
    console.error(err);
  }

  form.find('[type="submit"] .spinner').hide();

  submissions[hook] = false;
};

$(document).on('submit', '[data-form-hook]', submitMemberForm);
