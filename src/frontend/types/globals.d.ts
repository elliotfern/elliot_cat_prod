interface TrixBlockAttribute {
  tagName: string;
  terminal: boolean;
  breakOnReturn: boolean;
  group: boolean;
}

interface TrixConfig {
  blockAttributes: Record<string, TrixBlockAttribute>;
}

declare const Trix: {
  config: TrixConfig;
};
