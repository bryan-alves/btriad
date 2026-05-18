/** Tamanho final da foto de perfil (quadrado 1:1). */
export const PHOTO_OUTPUT_SIZE = 512

const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']

export function validatePhotoFile(file: File): string | null {
  if (!ACCEPTED_TYPES.includes(file.type)) {
    return 'Use JPG, PNG, WebP ou GIF.'
  }
  if (file.size > 2 * 1024 * 1024) {
    return 'A imagem deve ter no máximo 2 MB.'
  }
  return null
}

export function blobToCroppedPhotoFile(blob: Blob): File {
  return new File([blob], `foto-${Date.now()}.jpg`, {
    type: 'image/jpeg',
    lastModified: Date.now(),
  })
}
