/** Tamanho final padrão (foto de perfil 1:1). */
export const PHOTO_OUTPUT_SIZE = 512

const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']

export function validatePhotoFile(file: File, maxMb = 2): string | null {
  if (!ACCEPTED_TYPES.includes(file.type)) {
    return 'Use JPG, PNG, WebP ou GIF.'
  }
  if (file.size > maxMb * 1024 * 1024) {
    return `A imagem deve ter no máximo ${maxMb} MB.`
  }
  return null
}

export function blobToCroppedImageFile(blob: Blob, prefix = 'foto'): File {
  return new File([blob], `${prefix}-${Date.now()}.jpg`, {
    type: 'image/jpeg',
    lastModified: Date.now(),
  })
}

/** @deprecated Use blobToCroppedImageFile */
export function blobToCroppedPhotoFile(blob: Blob): File {
  return blobToCroppedImageFile(blob, 'foto')
}

export type CropPreset = {
  aspectRatio: number | null
  outputWidth: number
  outputHeight: number
  title: string
  hint: string
  filenamePrefix: string
}

export const CROP_PRESETS = {
  profile: {
    aspectRatio: 1,
    outputWidth: 512,
    outputHeight: 512,
    title: 'Ajustar foto (1:1)',
    hint: 'Enquadre no quadrado. A foto será usada em formato 1:1.',
    filenamePrefix: 'foto',
  },
  navLogo: {
    aspectRatio: 3,
    outputWidth: 600,
    outputHeight: 200,
    title: 'Ajustar logo do header (3:1)',
    hint: 'Logo horizontal da barra de navegação. Proporção 3:1.',
    filenamePrefix: 'logo-header',
  },
  footerLogo: {
    aspectRatio: 3,
    outputWidth: 480,
    outputHeight: 160,
    title: 'Ajustar logo do footer (3:1)',
    hint: 'Logo exibido no rodapé. Proporção 3:1.',
    filenamePrefix: 'logo-footer',
  },
  heroLogo: {
    aspectRatio: null,
    outputWidth: 800,
    outputHeight: 800,
    title: 'Ajustar logo da seção Sobre',
    hint: 'Logo central da seção Sobre (quando não houver carrossel). Recorte livre.',
    filenamePrefix: 'logo-hero',
  },
  appLogo: {
    aspectRatio: 1,
    outputWidth: 512,
    outputHeight: 512,
    title: 'Ajustar logo do sistema (1:1)',
    hint: 'Usado no login e no menu lateral do painel. Proporção 1:1.',
    filenamePrefix: 'logo-app',
  },
  carousel: {
    aspectRatio: 16 / 9,
    outputWidth: 1920,
    outputHeight: 1080,
    title: 'Ajustar foto do carrossel (16:9)',
    hint: 'Banner largo da seção Sobre. Proporção 16:9.',
    filenamePrefix: 'carousel',
  },
} satisfies Record<string, CropPreset>
