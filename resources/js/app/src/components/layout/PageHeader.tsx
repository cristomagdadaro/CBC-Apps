import React from 'react';
import { Link } from 'react-router-dom';
import { ChevronLeft } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

interface PageHeaderProps {
  title: string;
  subtitle?: string;
  actions?: React.ReactNode;
  backLink?: string;
  backLabel?: string;
  className?: string;
}

export function PageHeader({
  title,
  subtitle,
  actions,
  backLink,
  backLabel = 'Back',
  className,
}: PageHeaderProps) {
  return (
    <div className={cn('mb-6', className)}>
      {backLink && (
        <Button
          variant="ghost"
          size="sm"
          asChild
          className="mb-2 -ml-2 text-gray-500 hover:text-gray-900"
        >
          <Link to={backLink}>
            <ChevronLeft className="w-4 h-4 mr-1" />
            {backLabel}
          </Link>
        </Button>
      )}
      
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
          {subtitle && (
            <p className="mt-1 text-sm text-gray-500">{subtitle}</p>
          )}
        </div>
        
        {actions && (
          <div className="flex items-center gap-2">
            {actions}
          </div>
        )}
      </div>
    </div>
  );
}
