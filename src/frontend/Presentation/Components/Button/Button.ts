export class Button {
  private static base(label: string, url: string, classes: string): HTMLAnchorElement {
    const a = document.createElement('a');

    a.href = url;
    a.className = classes;
    a.textContent = label;

    return a;
  }

  private static action(label: string, classes: string, attributes: Record<string, string>): HTMLButtonElement {
    const button = document.createElement('button');

    button.type = 'button';
    button.className = classes;
    button.textContent = label;

    Object.entries(attributes).forEach(([key, value]) => {
      button.dataset[key] = value;
    });

    return button;
  }

  static create(label: string, url: string): HTMLAnchorElement {
    return this.base(label, url, 'btn btn-secondary btn-sm');
  }

  static edit(label: string, url: string): HTMLAnchorElement {
    return this.base(label, url, 'btn btn-warning btn-sm');
  }

  static edit2(label: string, url: string): HTMLAnchorElement {
    return this.base(label, url, 'btn btn-success btn-sm');
  }

  static delete(label: string, id: string, deleteHandler: string, reloadCallback?: string): HTMLButtonElement {
    const attributes: Record<string, string> = {
      id,
      deleteHandler,
    };

    if (reloadCallback) {
      attributes.reloadCallback = reloadCallback;
    }

    return this.action(label, 'delete-button btn btn-danger btn-sm', attributes);
  }
}
