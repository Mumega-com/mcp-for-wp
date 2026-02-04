/**
 * Extensions Index
 * Export all available extensions
 */

export { BaseExtension } from "./base.js";
export { CoreExtension } from "./core.js";
export { SEOExtension } from "./seo.js";
export { FormsExtension } from "./forms.js";
export { ElementorExtension } from "./elementor.js";

// Extension registry for dynamic loading
import { Extension } from "../types/index.js";
import { CoreExtension } from "./core.js";
import { SEOExtension } from "./seo.js";
import { FormsExtension } from "./forms.js";
import { ElementorExtension } from "./elementor.js";

export const BuiltInExtensions: Record<string, new () => Extension> = {
  core: CoreExtension,
  seo: SEOExtension,
  forms: FormsExtension,
  elementor: ElementorExtension,
};

// Helper to load all built-in extensions
export function createAllExtensions(): Extension[] {
  return [
    new CoreExtension(),
    new SEOExtension(),
    new FormsExtension(),
    new ElementorExtension(),
  ];
}

// Helper to load specific extensions by name
export function createExtensions(names: string[]): Extension[] {
  return names.map((name) => {
    const ExtClass = BuiltInExtensions[name];
    if (!ExtClass) {
      throw new Error(`Unknown extension: ${name}`);
    }
    return new ExtClass();
  });
}
