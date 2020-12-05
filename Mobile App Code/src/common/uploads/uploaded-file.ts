import { getFileMime } from './utils/get-file-mime';
import { extensionFromFilename } from './utils/extension-from-filename';

export class UploadedFile {
    name: string;
    relativePath: string;
    size: number;
    mime: string;
    extension: string;
    native: File;
    lastModified: number;
    url: string|null = null;

    constructor(file: File, relativePath?: string) {
        this.name = file.name;
        this.size = file.size;
        this.mime = getFileMime(file);
        this.lastModified = file.lastModified;
        this.extension = extensionFromFilename(file.name);
        this.native = file;
        this.relativePath = relativePath || file.webkitRelativePath || null;
    }

    public getData(): Promise<string> {
        return new Promise(resolve => {
            const reader = new FileReader();

            reader.addEventListener('load', () => {
                resolve(reader.result as string);
            });

            if (this.extension === 'json') {
                reader.readAsText(this.native);
            } else {
                reader.readAsDataURL(this.native);
            }
        });
    }
}
