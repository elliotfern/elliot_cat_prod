export class Button {
  private static base(label: string, url: string, classes: string): HTMLAnchorElement {
    const a = document.createElement('a');
    a.href = url;
    a.className = classes;
    a.textContent = label;
    return a;
  }

  static create(label: string, url: string): HTMLAnchorElement {
    return this.base(label, url, 'btn btn-secondary btn-sm');
  }

  static edit(label: string, url: string): HTMLAnchorElement {
    return this.base(label, url, 'btn btn-warning btn-sm');
  }

  static delete(label: string, url: string): HTMLAnchorElement {
    return this.base(label, url, 'btn btn-danger btn-sm');
  }
}
